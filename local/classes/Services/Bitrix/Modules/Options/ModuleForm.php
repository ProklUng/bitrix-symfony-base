<?php

namespace Local\Services\Bitrix\Modules\Options;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use CAdminMessage;
use CAdminTabControl;
use Bitrix\Main\Application;
use Exception;

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/options.php');

/**
 * Class ModuleForm
 * @package Local\Services\Bitrix\Modules\Option
 *
 * @since 13.04.2021
 */
class ModuleForm
{
    /**
     * @var ModuleManager $options Менеджер опций.
     */
    private $options;

    /**
     * @var Context $context Контекст.
     */
    private $context;

    /**
     * @var string|null $formId ID формы опций.
     */
    private $formId;

    /**
     * ModuleForm constructor.
     *
     * @param ModuleManager $options Опции.
     * @param string|null   $formId  ID формы опций.
     */
    public function __construct(ModuleManager $options, ?string $formId = 'module_settings_form')
    {
        global $USER, $APPLICATION;
        if (!$USER->IsAdmin()) {
            $APPLICATION->AuthForm('Access denied.');
        }

        $this->options = $options;
        $this->context = Application::getInstance()->getContext();
        $this->formId = $formId;

        defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', $this->options->getModuleId());
    }

    /**
     * Handle options save request.
     *
     * @return void
     * @throws ArgumentNullException Когда что-то пошло не так.
     */
    public function handleRequest(): void
    {
        global $save, $restore;

        $request = $this->getRequest();
        if ((!empty($save) || !empty($restore)) && $request->isPost() && check_bitrix_sessid()) {
            if (!empty($restore)) {
                // Restore defaults
                Option::delete(ADMIN_MODULE_NAME);
                CAdminMessage::showMessage([
                    'MESSAGE' => 'Параметры установлены по умолчанию.',
                    'TYPE' => 'OK',
                ]);
            } else {
                try {
                    // Save options
                    foreach ($this->options->getFields() as $id => $opt) {
                        switch ($opt['type']) {
                            case 'checkbox':
                                $value = $request->getPost($id) ? true : false;
                                break;

                            case 'number':
                                $value = (int)$request->getPost($id);
                                break;

                            case 'text':
                            default:
                                $value = $request->getPost($id);
                                break;
                        }

                        $event = new Event(ADMIN_MODULE_NAME, 'OnBeforeSetOption_' . $id, ['value' => &$value]);
                        $event->send();

                        $event = new Event(ADMIN_MODULE_NAME, 'OnBeforeSetOption', ['value' => &$value, 'name' => $id]);
                        $event->send();

                        Option::set(ADMIN_MODULE_NAME, $id, $value);
                    }

                    $event = new Event(ADMIN_MODULE_NAME, 'OnAfterSaveOptions');
                    $event->send();

                    CAdminMessage::showMessage([
                        'MESSAGE' => 'Параметры сохранены',
                        'TYPE' => 'OK',
                    ]);
                } catch (Exception $exception) {
                    CAdminMessage::showMessage($exception->getMessage());
                }
            }
        }
    }

    /**
     * Output options form.
     *
     * @return void
     * @throws ArgumentNullException Когда что-то пошло не так.
     */
    public function write(): void
    {
        global $mid;

        $tabControl = new CAdminTabControl('tabControl', $this->options->getTabs());

        $fields = $this->options->getFields();

        $tabControl->begin();
        ?>
        <form
            method="post"
            action="<?= sprintf('%s?mid=%s&lang=%s', $this->getRequest()->getRequestedPage(), urlencode($mid), LANGUAGE_ID) ?>"
            name="<?= $this->formId ?>"
            id="<?= $this->formId ?>"
        >
            <?php
            echo bitrix_sessid_post();
            foreach ($tabControl->tabs as $tab) {
                $tabControl->beginNextTab();
                $filteredOpts = array_filter($fields, static function ($opt) use ($tab) {
                    return $opt['tab'] === $tab['DIV'];
                });

                foreach ($filteredOpts as $opt_name => $opt) { ?>
                    <tr id="<?= $opt_name ?>_row">
                        <?php
                        switch ($opt['type']) {
                            case 'html':
                                if (!empty($opt['label'])) {
                                    ?>
                                    <td width="30%" style="vertical-align: top; line-height: 25px;">
                                        <label for="<?= $opt_name ?>">
                                            <?= $opt['label'] . ($opt['required'] ? ' *' : '') ?>:
                                        </label>
                                    </td>
                                    <td width="70%">
                                    <?php
                                } else {
                                    ?>
                                    <td colspan="2">
                                    <?php
                                }

                                if ($opt['html']) {
                                    echo $opt['html'];
                                } elseif (file_exists($opt['path'])) {
                                    /** @noinspection PhpIncludeInspection */
                                    include $opt['path'];
                                } elseif (isset($opt['render']) && is_callable($opt['render'])) {
                                    echo $opt['render']();
                                }
                                ?>
                                </td>
                                <?php
                                break;

                            default:
                                ?>
                                <td width="30%" style="vertical-align: top; line-height: 25px;">
                                    <label for="<?= $opt_name ?>">
                                        <?= $opt['label'] . ($opt['required'] ? ' *' : '') ?>:
                                    </label>
                                </td>
                                <td width="70%">
                                    <?php
                                    switch ($opt['type']) {
                                        case 'select':
                                            ?>
                                            <select
                                                name="<?= $opt_name ?>"
                                                id="<?= $opt_name ?>"
                                                <?= $opt['required'] ? 'required="required"' : '' ?>
                                            >
                                                <option
                                                    value=""
                                                    <?= $opt['required'] ? ' disabled' : '' ?>
                                                    <?= !Option::get(ADMIN_MODULE_NAME, $opt_name, $opt['default']) ? 'selected="selected"' : '' ?>
                                                >-</option>
                                                <?php foreach ($opt['values'] as $value => $display) { ?>
                                                    <option
                                                        value="<?= $value ?>"
                                                        <?= Option::get(ADMIN_MODULE_NAME, $opt_name, $opt['default']) == $value ? 'selected="selected"' : '' ?>
                                                    ><?= $display ?></option>
                                                <?php } ?>
                                            </select>
                                            <?php
                                            break;

                                        case 'checkbox':
                                            ?>
                                            <input
                                                type="<?= $opt['type'] ?>"
                                                name="<?= $opt_name ?>"
                                                id="<?= $opt_name ?>"
                                                <?= Option::get(ADMIN_MODULE_NAME, $opt_name) ? 'checked="checked"' : '' ?>
                                                <?= $opt['required'] ? 'required="required"' : '' ?>
                                            />
                                            <?php
                                            break;

                                        case 'textarea':
                                            ?>
                                            <textarea
                                                cols="<?= $opt['cols'] ?: '80' ?>"
                                                rows="<?= $opt['rows'] ?: '20' ?>"
                                                name="<?= $opt_name ?>"
                                                id="<?= $opt_name ?>"
												<?= $opt['required'] ? 'required="required"' : '' ?>
                                                <?= $opt['placeholder'] ? 'placeholder="' . htmlspecialchars($opt['placeholder']) . '"' : '' ?>
											><?= htmlspecialchars(Option::get(ADMIN_MODULE_NAME, $opt_name, $opt['default'])); ?></textarea>
                                            <?php
                                            break;

                                        case 'text':
                                        default:
                                            ?>
                                            <input
                                                type="<?= $opt['type'] ?>"
                                                size="<?= $opt['size'] ?: '50' ?>"
                                                name="<?= $opt_name ?>"
                                                id="<?= $opt_name ?>"
                                                value="<?= htmlspecialchars(Option::get(ADMIN_MODULE_NAME, $opt_name)); ?>"
                                                <?= $opt['required'] ? 'required="required"' : '' ?>
                                                <?= $opt['placeholder'] ? 'placeholder="' . htmlspecialchars($opt['placeholder']) . '"' : '' ?>
                                            />
                                            <?php
                                            break;
                                    }
                                    ?>
                                    <?= $opt['description'] ? '<p>' . $opt['description'] . '</p>' : '' ?>
                                </td>
                                <?php
                                break;
                        } ?>
                    </tr>
                <?php }
            }

            $tabControl->buttons();
            ?>
            <input
                type="submit"
                name="save"
                value="<?= Loc::getMessage('MAIN_SAVE') ?>"
                title="<?= Loc::getMessage('MAIN_OPT_SAVE_TITLE') ?>"
                class="adm-btn-save"
            />

            <input
                type="submit"
                name="restore"
                title="<?= Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS') ?>"
                onclick="return confirm('<?= addslashes(GetMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING')) ?>')"
                value="<?= Loc::getMessage('MAIN_RESTORE_DEFAULTS') ?>"
            />
            <?php
            $tabControl->end();
            ?>
        </form>
        <?php
    }

    /**
     * Request.
     *
     * @return HttpRequest
     */
    private function getRequest(): HttpRequest
    {
        return $this->context->getRequest();
    }
}
