<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties;

use CFile;
use CFileInput;
use CIBlockElement;
use CIBlockSection;
use CJSCore;
use CModule;
use COption;
use Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction\IblockPropertyTypeNativeInterface;

/**
 * Class CIBlockPropertyCProp
 * Комплексное свойство инфоблока.
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties
 *
 * @since 10.02.2021
 * @since 10.03.2021 Фикс ошибки загрузки файлов.
 * @since 13.03.2021 Добавил тип поля - привязка к подразделу.
 *
 * @see Модуль kit.cprop. Вытащено из модуля. Причесано.
 */
class CIBlockPropertyCProp implements IblockPropertyTypeNativeInterface
{
    /**
     * @var boolean $showedCss
     */
    private static $showedCss = false;

    /**
     * @var boolean $showedJs
     */
    private static $showedJs = false;

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        AddEventHandler(
            'iblock',
            "OnIBlockPropertyBuildList",
            [__CLASS__, "GetUserTypeDescription"]
        );
    }

    /**
     * @return array
     */
    public function GetUserTypeDescription(): array
    {
        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'C',
            'DESCRIPTION' => 'Комплексное свойство',
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
            'ConvertFromDB' => [__CLASS__, 'ConvertFromDB'],
            'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
            'PrepareSettings' => [__CLASS__, 'PrepareUserSettings'],
            'GetLength' => [__CLASS__, 'GetLength'],
            'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
        ];
    }

    /**
     * @param array $arProperty
     * @param mixed $value
     * @param mixed $strHTMLControlName
     *
     * @return string
     */
    public function GetPropertyFieldHtml(array $arProperty, $value, $strHTMLControlName) : string
    {
        $hideText = 'Показать';
        $clearText = 'Свернуть';

        self::showCss();
        self::showJs();

        if (!empty($arProperty['USER_TYPE_SETTINGS'])) {
            $arFields = self::prepareSetting((array)$arProperty['USER_TYPE_SETTINGS']);
        } else {
            return '<span>Не заполнен список полей в настройках свойства</span>';
        }

        $result = '';

        $result .= '<div class="mf-gray"><a class="cl mf-toggle">'.$hideText.'</a>';
        if ($arProperty['MULTIPLE'] === 'Y') {
            $result .= ' | <a class="cl mf-delete">'.$clearText.'</a></div>';
        }
        $result .= '<table class="mf-fields-list active">';

        foreach ($arFields as $code => $arItem) {
            if ($arItem['TYPE'] === 'string') {
                $result .= self::showString($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'file') {
                $result .= self::showFile($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'text') {
                $result .= self::showTextarea($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'date') {
                $result .= self::showDate($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'element') {
                $result .= self::showBindElement($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'group') {
                $result .= self::showBindSection($code, $arItem['TITLE'], $value, $strHTMLControlName);
            }
        }

        $result .= '</table>';

        return $result;
    }

    /**
     * @param array $arProperty
     * @param mixed $value
     * @param mixed $strHTMLControlName
     *
     * @return string
     */
    public static function GetPublicViewHTML(array $arProperty, $value, $strHTMLControlName) : string
    {
        $result = '';

        $arFields = [];
        if (!empty($arProperty['USER_TYPE_SETTINGS'])) {
            $arFields = self::prepareSetting((array)$arProperty['USER_TYPE_SETTINGS']);
        }

        if (!empty($value['VALUE'])) {
            $result .= '<br>';

            $data = json_decode($value['VALUE'], true);
            foreach ($data as $code => $value) {
                $title = $arFields[$code]['TITLE'];
                $type = $arFields[$code]['TYPE'];

                if ($type === 'string') {
                    $result .= $title.': '.$value.'<br>';
                } elseif ($type === 'date') {
                    $result .= $title.': '.$value.'<br>';
                }
            }
        }

        return $result;
    }

    /**
     * @param array $arProperty
     * @param array $strHTMLControlName
     * @param array $arPropertyFields
     *
     * @return string
     */
    public static function GetSettingsHTML(array $arProperty, array $strHTMLControlName, array &$arPropertyFields) : string
    {
        $btnAdd = 'Добавить';
        $settingsTitle = 'Список полей';

        $arPropertyFields = [
            'USER_TYPE_SETTINGS_TITLE' => $settingsTitle,
            'HIDE' => [
                'ROW_COUNT',
                'COL_COUNT',
                'DEFAULT_VALUE',
                'SMART_FILTER',
                'WITH_DESCRIPTION',
                'FILTRABLE',
                'MULTIPLE_CNT',
                'IS_REQUIRED',
            ],
            'SET' => [
                'MULTIPLE_CNT' => 1,
                'SMART_FILTER' => 'N',
                'FILTRABLE' => 'N',
                'SEARCHABLE' => 1,
            ],
        ];

        self::showJsForSetting($strHTMLControlName['NAME']);
        self::showCssForSetting();

        $result = '<tr><td colspan="2" align="center">
            <table id="many-fields-table" class="many-fields-table internal">        
                <tr valign="top" class="heading mf-setting-title">
                   <td>XML_ID</td>
                   <td>Название</td>
                   <td>Сорт</td>
                   <td>Тип</td>
                </tr>';


        $arSetting = self::prepareSetting((array)$arProperty['USER_TYPE_SETTINGS']);

        if (!empty($arSetting)) {
            foreach ($arSetting as $code => $arItem) {
                $result .= '
                       <tr valign="top">
                           <td><input type="text" class="inp-code" size="20" value="'.$code.'"></td>
                           <td><input type="text" class="inp-title" size="35" name="'.$strHTMLControlName['NAME'].'['.$code.'_TITLE]" value="'.$arItem['TITLE'].'"></td>
                           <td><input type="text" class="inp-sort" size="5" name="'.$strHTMLControlName['NAME'].'['.$code.'_SORT]" value="'.$arItem['SORT'].'"></td>
                           <td>
                                <select class="inp-type" name="'.$strHTMLControlName['NAME'].'['.$code.'_TYPE]">
                                    '.self::getOptionList($arItem['TYPE']).'
                                </select>                        
                           </td>
                       </tr>';
            }
        }

        $result .= '
               <tr valign="top">
                    <td><input type="text" class="inp-code" size="20"></td>
                    <td><input type="text" class="inp-title" size="35"></td>
                    <td><input type="text" class="inp-sort" size="5" value="500"></td>
                    <td>
                        <select class="inp-type"> '.self::getOptionList().'</select>                        
                    </td>
               </tr>
             </table>   
                
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <input type="button" value="'.$btnAdd.'" onclick="addNewRows()">
                    </td>
                </tr>
                </td></tr>';

        return $result;
    }

    /**
     * @param array $arProperty
     *
     * @return array
     */
    public static function PrepareUserSettings(array $arProperty) : array
    {
        $result = [];
        if (!empty($arProperty['USER_TYPE_SETTINGS'])) {
            foreach ($arProperty['USER_TYPE_SETTINGS'] as $code => $value) {
                $result[$code] = $value;
            }
        }

        return $result;
    }

    /**
     * @param array $arProperty
     * @param array $arValue
     *
     * @return boolean
     */
    public static function GetLength($arProperty, $arValue) : bool
    {
        $arFields = self::prepareSetting(
            unserialize($arProperty['USER_TYPE_SETTINGS'])
        );

        $result = false;
        foreach ($arValue['VALUE'] as $code => $value) {
            if ($arFields[$code]['TYPE'] === 'file') {
                if (!empty($value['name']) || (!empty($value['OLD']) && empty($value['DEL']))) {
                    $result = true;
                    break;
                }
            } else {
                if (!empty($value)) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param array $arProperty
     * @param array $arValue
     *
     * @return string[]
     */
    public function ConvertToDB(array $arProperty, array $arValue) : array
    {
        $arResult= [];
        $arFields = self::prepareSetting((array)$arProperty['USER_TYPE_SETTINGS']);

        foreach ($arValue['VALUE'] as $code => $value) {
            if ($arFields[$code]['TYPE'] === 'file') {
                $arValue['VALUE'][$code] = self::prepareFileToDB($value);
            }
        }

        $isEmpty = true;
        foreach ($arValue['VALUE'] as $v) {
            if (!empty($v)) {
                $isEmpty = false;
                break;
            }
        }

        if ($isEmpty === false) {
            $arResult['VALUE'] = json_encode($arValue['VALUE']);
        } else {
            $arResult = ['VALUE' => '', 'DESCRIPTION' => ''];
        }

        return $arResult;
    }

    /**
     * @param array $arProperty
     * @param array $arValue
     *
     * @return array
     */
    public function ConvertFromDB($arProperty, $arValue) : array
    {
        $return = [];

        if (!empty($arValue['VALUE'])) {
            $arData = json_decode($arValue['VALUE'], true);

            foreach ($arData as $code => $value) {
                $return['VALUE'][$code] = $value;
            }

        }

        return $return;
    }

    /**
     * @param string $code
     * @param string $title
     * @param array  $arValue
     * @param array  $strHTMLControlName
     *
     * @return string
     */
    private static function showString(string $code, string $title, array $arValue, array $strHTMLControlName) : string
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? htmlspecialchars($arValue['VALUE'][$code]) : '';
        $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td><input type="text" value="'.$v.'" name="'.$strHTMLControlName['VALUE'].'['.$code.']"/></td>
                </tr>';

        return $result;
    }

    /**
     * @param string $code
     * @param string $title
     * @param array  $arValue
     * @param array  $strHTMLControlName
     *
     * @return string
     */
    private static function showFile(string $code, string $title, array $arValue, array $strHTMLControlName) : string
    {
        $result = '';

        if (!empty($arValue['VALUE'][$code]) && !is_array($arValue['VALUE'][$code])) {
            $fileId = $arValue['VALUE'][$code];
        } else {
            $fileId = '';
            if (!empty($arValue['VALUE'][$code]['OLD'])) {
                $fileId = $arValue['VALUE'][$code]['OLD'];
            }
        }

        if (!empty($fileId)) {
            $arPicture = CFile::GetByID($fileId)->Fetch();
            if ($arPicture) {
                $strImageStorePath = COption::GetOptionString('main', 'upload_dir', 'upload');
                $sImagePath = '/'.$strImageStorePath.'/'.$arPicture['SUBDIR'].'/'.$arPicture['FILE_NAME'];
                $fileType = self::getExtension($sImagePath);

                if (in_array($fileType, ['png', 'jpg', 'jpeg', 'gif'])) {
                    $content = '<img src="'.$sImagePath.'">';
                } else {
                    $content = '<div class="mf-file-name">'.$arPicture['FILE_NAME'].'</div>';
                }

                $result = '<tr>
                        <td align="right" valign="top">'.$title.': </td>
                        <td>
                            <table class="mf-img-table">
                                <tr>
                                    <td>'.$content.'<br>
                                        <div>
                                            <label><input name="'.$strHTMLControlName['VALUE'].'['.$code.'][DEL]" value="Y" type="checkbox">Удалить файл</label>
                                            <input name="'.$strHTMLControlName['VALUE'].'['.$code.'][OLD]" value="'.$fileId.'" type="hidden">
                                        </div>
                                    </td>
                                </tr>
                            </table>                      
                        </td>
                    </tr>';
            }
        } else {
            $data = '';

            if ($strHTMLControlName["MODE"] === "FORM_FILL" && CModule::IncludeModule('fileman')) {
                $inputName = $strHTMLControlName['VALUE'].'['.$code.']';
                $data = CFileInput::Show($inputName, $fileId,
                    [
                        "PATH" => "Y",
                        "IMAGE" => "Y",
                        "MAX_SIZE" => [
                            "W" => COption::GetOptionString("iblock", "detail_image_size"),
                            "H" => COption::GetOptionString("iblock", "detail_image_size"),
                        ],
                    ], [
                        'upload' => true,
                        'medialib' => true,
                        'file_dialog' => true,
                        'cloud' => true,
                        'del' => false,
                        'description' => false,
                    ]
                );
            }


            $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td>'.$data.'</td>
                </tr>';
        }

        return $result;
    }

    /**
     * @param string $code
     * @param string $title
     * @param array  $arValue
     * @param array  $strHTMLControlName
     *
     * @return string
     */
    public static function showTextarea(string $code, string $title, array $arValue, array $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $result .= '<tr>
                    <td align="right" valign="top">'.$title.': </td>
                    <td><textarea rows="8" name="'.$strHTMLControlName['VALUE'].'['.$code.']">'.$v.'</textarea></td>
                </tr>';

        return $result;
    }

    /**
     * @param string $code
     * @param string $title
     * @param array  $arValue
     * @param array  $strHTMLControlName
     *
     * @return string
     */
    public static function showDate(string $code, string $title, array $arValue, array $strHTMLControlName) : string
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $result .= '<tr>
                        <td align="right" valign="top">'.$title.': </td>
                        <td>
                            <table>
                                <tr>
                                    <td style="padding: 0;">
                                        <div class="adm-input-wrap adm-input-wrap-calendar">
                                            <input class="adm-input adm-input-calendar" type="text" name="'.$strHTMLControlName['VALUE'].'['.$code.']" size="23" value="'.$v.'">
                                            <span class="adm-calendar-icon"
                                                  onclick="BX.calendar({node: this, field:\''.$strHTMLControlName['VALUE'].'['.$code.']\', form: \'\', bTime: true, bHideTime: false});"></span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';

        return $result;
    }

    /**
     * @param string $code
     * @param string $title
     * @param array  $arValue
     * @param array  $strHTMLControlName
     *
     * @return string
     */
    public static function showBindElement(string $code, string $title, array $arValue, array $strHTMLControlName) : string
    {
        $result = '';

        $id = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';

        $elUrl = '';
        if (!empty($id)) {
            /** @psalm-suppress PossiblyInvalidMethodCall */
            $arElem = CIBlockElement::GetList([], ['ID' => $id], false, ['nPageSize' => 1],
                ['ID', 'IBLOCK_ID', 'IBLOCK_TYPE_ID', 'NAME'])->Fetch();
            if (!empty($arElem)) {
                $elUrl .= '<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$arElem['IBLOCK_ID'].'&ID='.$arElem['ID'].'&type='.$arElem['IBLOCK_TYPE_ID'].'">'.$arElem['NAME'].'</a>';
            }
        }

        $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td>
                        <input name="'.$strHTMLControlName['VALUE'].'['.$code.']" id="'.$strHTMLControlName['VALUE'].'['.$code.']" value="'.$id.'" size="8" type="text" class="mf-inp-bind-elem">
                        <input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=ru&IBLOCK_ID=0&n='.$strHTMLControlName['VALUE'].'&k='.$code.'\', 900, 700);">&nbsp;
                        <span>'.$elUrl.'</span>
                    </td>
                </tr>';

        return $result;
    }

    /**
     * @param string $code
     * @param string $title
     * @param array  $arValue
     * @param array  $strHTMLControlName
     *
     * @return string
     */
    public static function showBindSection(string $code, string $title, array $arValue, array $strHTMLControlName) : string
    {
        $result = '';

        $id = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';

        $elUrl = '';
        if (!empty($id)) {
            /** @psalm-suppress PossiblyInvalidMethodCall */
            $arElem = CIBlockSection::GetList([], ['ID' => $id], false, [],
                ['ID', 'IBLOCK_ID', 'IBLOCK_TYPE_ID', 'NAME'])->Fetch();
            if (!empty($arElem)) {
                $elUrl .= '<a target="_blank" href="/bitrix/admin/iblock_section_search.php?IBLOCK_ID='.$arElem['IBLOCK_ID'].'&ID='.$arElem['ID'].'&type='.$arElem['IBLOCK_TYPE_ID'].'">'.$arElem['NAME'].'</a>';
            }
        }

        $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td>
                        <input name="'.$strHTMLControlName['VALUE'].'['.$code.']" id="'.$strHTMLControlName['VALUE'].'['.$code.']" value="'.$id.'" size="8" type="text" class="mf-inp-bind-elem">
                        <input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_section_search.php?lang=ru&IBLOCK_ID=0&n='.$strHTMLControlName['VALUE'].'&k='.$code.'\', 900, 700);">&nbsp;
                        <span>'.$elUrl.'</span>
                    </td>
                </tr>';

        return $result;
    }

    /**
     * @return void
     */
    private static function showCss() : void
    {
        if (!self::$showedCss) {
            self::$showedCss = true;
            ?>
          <style>
              .cl {
                  cursor: pointer;
              }

              .mf-gray {
                  color: #797777;
              }

              .mf-fields-list {
                  display: none;
                  padding-top: 10px;
                  margin-bottom: 10px !important;
                  margin-left: -300px !important;
                  border-bottom: 1px #e0e8ea solid !important;
              }

              .mf-fields-list.active {
                  display: block;
              }

              .mf-fields-list td {
                  padding-bottom: 5px;
              }

              .mf-fields-list td:first-child {
                  width: 300px;
                  color: #616060;
              }

              .mf-fields-list td:last-child {
                  padding-left: 5px;
              }

              .mf-fields-list input[type="text"] {
                  width: 350px !important;
              }

              .mf-fields-list textarea {
                  min-width: 350px;
                  max-width: 650px;
                  color: #000;
              }

              .mf-fields-list img {
                  max-height: 150px;
                  margin: 5px 0;
              }

              .mf-img-table {
                  background-color: #e0e8e9;
                  color: #616060;
                  width: 100%;
              }

              .mf-fields-list input[type="text"].adm-input-calendar {
                  width: 170px !important;
              }

              .mf-file-name {
                  word-break: break-word;
                  padding: 5px 5px 0 0;
                  color: #101010;
              }

              .mf-fields-list input[type="text"].mf-inp-bind-elem {
                  width: unset !important;
              }
          </style>
            <?
        }
    }

    /**
     * @return void
     */
    private static function showJs() : void
    {
        $showText = 'Показать';
        $hideText = 'Свернуть';

        CJSCore::Init(['jquery']);
        if (!self::$showedJs) {
            self::$showedJs = true;
            ?>
          <script>
              $(document).on('click', 'a.mf-toggle', function (e) {
                  e.preventDefault()

                  var table = $(this).closest('tr').find('table.mf-fields-list')
                  $(table).toggleClass('active')
                  if ($(table).hasClass('active')) {
                      $(this).text('<?=$hideText?>')
                  } else {
                      $(this).text('<?=$showText?>')
                  }
              })

              $(document).on('click', 'a.mf-delete', function (e) {
                  e.preventDefault()

                  var textInputs = $(this).closest('tr').find('input[type="text"]')
                  $(textInputs).each(function (i, item) {
                      $(item).val('')
                  })

                  var textarea = $(this).closest('tr').find('textarea')
                  $(textarea).each(function (i, item) {
                      $(item).text('')
                  })

                  var checkBoxInputs = $(this).closest('tr').find('input[type="checkbox"]')
                  $(checkBoxInputs).each(function (i, item) {
                      $(item).attr('checked', 'checked')
                  })

                  $(this).closest('tr').hide('slow')
              })

              // This is for multiple file type property (crutch)
              BX.ready(function () {
                  BX.addCustomEvent('onAddNewRowBeforeInner', function (data) {
                      var html_string = data.html

                      // If cloned property of cprop
                      if ($('<div>' + html_string + '</div>').find('table.mf-fields-list').length > 0) {

                          var blocks = $(html_string).find('.adm-input-file-control.adm-input-file-top-shift')
                          if (blocks.length > 0) {

                              document.cprop_endPos = 0
                              $(blocks).each(function (i, item) {
                                  blockId = $(item).attr('id')

                                  if (blockId !== undefined && blockId !== null && blockId.length > 0) {
                                      setTimeout(function (i, blockId, html_string) {
                                          // Remove hidden inputs
                                          var inputs = $('#' + blockId + ' .adm-input-file-new')

                                          if (inputs !== undefined && inputs.length > 0) {
                                              inputs.each(function (i, item) {
                                                  $(item).remove()
                                              })
                                          }

                                          var start_pos = html_string.indexOf('new top.BX.file_input', document.cprop_endPos)
                                          var end_pos = html_string.indexOf(': new BX.file_input', start_pos)
                                          document.cprop_endPos = end_pos
                                          var jsCode = html_string.substring(start_pos, end_pos)

                                          eval(jsCode)
                                      }, 500, i, blockId, html_string)
                                  }
                              })
                              document.cprop_endPos = 0
                          }
                      }
                  })
              })
          </script>
            <?
        }
    }

    /**
     * @param string $inputName
     *
     * @return void
     */
    private static function showJsForSetting(string $inputName) : void
    {
        CJSCore::Init(['jquery']);
        ?>
      <script>
          function addNewRows () {
              $('#many-fields-table').append('' +
                  '<tr valign="top">' +
                  '<td><input type="text" class="inp-code" size="20"></td>' +
                  '<td><input type="text" class="inp-title" size="35"></td>' +
                  '<td><input type="text" class="inp-sort" size="5" value="500"></td>' +
                  '<td><select class="inp-type"><?=self::getOptionList()?></select></td>' +
                  '</tr>')
          }

          $(document).on('change', '.inp-code', function () {
              var code = $(this).val()

              if (code.length <= 0) {
                  $(this).closest('tr').find('input.inp-title').removeAttr('name')
                  $(this).closest('tr').find('input.inp-sort').removeAttr('name')
                  $(this).closest('tr').find('select.inp-type').removeAttr('name')
              } else {
                  $(this).closest('tr').find('input.inp-title').attr('name', '<?=$inputName?>[' + code + '_TITLE]')
                  $(this).closest('tr').find('input.inp-sort').attr('name', '<?=$inputName?>[' + code + '_SORT]')
                  $(this).closest('tr').find('select.inp-type').attr('name', '<?=$inputName?>[' + code + '_TYPE]')
              }
          })

          $(document).on('input', '.inp-sort', function () {
              var num = $(this).val()
              $(this).val(num.replace(/[^0-9]/gim, ''))
          })
      </script>
        <?php
    }

    /**
     * @return void
     */
    private static function showCssForSetting() : void
    {
        if (!self::$showedCss) {
            self::$showedCss = true;
            ?>
          <style>
              .many-fields-table {
                  margin: 0 auto; /*display: inline;*/
              }

              .mf-setting-title td {
                  text-align: center !important;
                  border-bottom: unset !important;
              }

              .many-fields-table td {
                  text-align: center;
              }

              .many-fields-table > input, .many-fields-table > select {
                  width: 90% !important;
              }

              .inp-sort {
                  text-align: center;
              }

              .inp-type {
                  min-width: 125px;
              }
          </style>
            <?
        }
    }

    /**
     * @param array $arSetting
     *
     * @return array
     */
    private static function prepareSetting(array $arSetting) : array
    {
        $arResult = [];

        foreach ($arSetting as $key => $value) {
            if (strpos($key, '_TITLE') !== false) {
                $code = str_replace('_TITLE', '', $key);
                $arResult[$code]['TITLE'] = $value;
            } elseif (strpos($key, '_SORT') !== false) {
                $code = str_replace('_SORT', '', $key);
                $arResult[$code]['SORT'] = $value;
            } elseif (strpos($key, '_TYPE') !== false) {
                $code = str_replace('_TYPE', '', $key);
                $arResult[$code]['TYPE'] = $value;
            }
        }

        uasort($arResult,
            /**
             * @param array $a
             * @param array $b
             * @return int
             */
            static function (array $a, array $b) : int {
                if ($a['SORT'] === $b['SORT']) {
                    return 0;
                }

                return ($a['SORT'] < $b['SORT']) ? -1 : 1;
            });

        return $arResult;
    }

    /**
     * @param string $selected
     *
     * @return string
     */
    private static function getOptionList(string $selected = 'string') : string
    {
        $result = '';
        $arOption = [
            'string' => 'Строка',
            'file' => 'Файл',
            'text' => 'Текст',
            'date' => 'Дата/Время',
            'element' => 'Привязка к элементу',
            'group' => 'Привязка к подразделу',
        ];

        foreach ($arOption as $code => $name) {
            $s = '';
            if ($code === $selected) {
                $s = 'selected';
            }

            $result .= '<option value="'.$code.'" '.$s.'>'.$name.'</option>';
        }

        return $result;
    }

    /**
     * @param mixed $arValue
     *
     * @return false|int|mixed|string
     *
     * @internal Нюанс: загрузка по ссылке - без домена!
     */
    private static function prepareFileToDB($arValue)
    {
        $result = false;

        if (!empty($arValue['DEL']) && $arValue['DEL'] === 'Y' && !empty($arValue['OLD'])) {
            CFile::Delete($arValue['OLD']);
        } elseif (!empty($arValue['OLD'])) {
            $result = $arValue['OLD'];
        } elseif (!empty($arValue['name'])) {
            $result = CFile::SaveFile($arValue, 'vote');
        } elseif (!empty($arValue) && is_file($_SERVER['DOCUMENT_ROOT'].$arValue)) {
            $arFile = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'].$arValue);
            $result = CFile::SaveFile($arFile, 'vote');
        }

        return $result;
    }

    /**
     * @param string $filePath Путь к файлу.
     *
     * @return mixed|string
     */
    private static function getExtension(string $filePath)
    {
        $exploded = explode('.', $filePath);

        return array_pop($exploded);
    }
}
