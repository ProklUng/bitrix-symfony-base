<?php

namespace Local\Bundles\BitrixWebformBundle\Services\Validators;

/**
 * Class CFormValidHttp
 * @package Local\Bundles\BitrixWebformBundle\Services\Validators
 *
 * @since 06.02.2021
 */
class CFormValidHttp extends AbstractCustomBitrixWebformValidator
{
    /**
     * @inheritDoc
     */
    public function GetDescription(): array
    {
        return [
            'NAME' => "valid_http",
            'DESCRIPTION' => 'Проверка http',
            'TYPES' => ['text', 'textarea'],
            'SETTINGS' => [$this, 'GetSettings'],
            'CONVERT_TO_DB' => [$this, 'ToDB'],
            'CONVERT_FROM_DB' => [$this, 'FromDB'],
            'HANDLER' => [$this, 'DoValidate'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function GetSettings(): array
    {
        return [
            'CHECK_HTTP' => [
                "TITLE" => "Не должен содержать (ftp|http|https)",
                "TYPE" => "CHECKBOX",
                "DEFAULT" => "Y",
            ],
            "CHECK_URL" => [
                "TITLE" => "Не должен содержать (domain.ru | домен.рф)",
                "TYPE" => "CHECKBOX",
                "DEFAULT" => "Y",
            ],
            "CHECK_LinkHrefUrl" => [
                "TITLE" => "Не должен содержать ([url] | [href] | [link])",
                "TYPE" => "CHECKBOX",
                "DEFAULT" => "Y",
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function ToDB($arParams)
    {
        $arParams['CHECK_HTTP'] = $arParams['CHECK_HTTP'] === "Y" ? "Y" : "N";
        $arParams['CHECK_URL'] = $arParams['CHECK_URL'] === "Y" ? "Y" : "N";
        $arParams['CHECK_LinkHrefUrl'] = $arParams["CHECK_LinkHrefUrl"] === "Y" ? "Y" : "N";

        return serialize($arParams);
    }

    /**
     * @inheritDoc
     */
    public function DoValidate($arParams, $arQuestion, $arAnswers, $arValues): bool
    {
        global $APPLICATION;
        // Регулярные выражения
        $urlPattern = "/(ftp|http|https):\/\/?/";
        $justURL = "/([a-zа-я]+)\.([a-zа-я]{2})/";
        $notLinkHrefUrl = "/(<a.+)(\/a>)|(\[url).+(\[url\])|(\[link).+(\[\/link\])/";

        foreach ($arValues as $value) {
            if (strlen($value) <= 0) {
                continue;
            }

            if ($arParams["CHECK_HTTP"] === "Y" && preg_match($urlPattern, $value)) {
                $APPLICATION->ThrowException("#FIELD_NAME#: Поле содержит ссылку");

                return false;
            }

            if ($arParams["CHECK_URL"] === "Y" && preg_match($justURL, $value)) {
                // вернем ошибку
                $APPLICATION->ThrowException("#FIELD_NAME#: Поле содержит ссылку");

                return false;
            }

            if ($arParams["CHECK_LinkHrefUrl"] === "Y" && preg_match($notLinkHrefUrl, $value)) {
                // вернем ошибку
                $APPLICATION->ThrowException("#FIELD_NAME#: Поле содержит недопустимые символы");

                return false;
            }
        }

        return true;
    }
}
