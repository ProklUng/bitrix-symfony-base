<?php
/**
 * Example of settings file config.php:
 * 
 * ```php
 * return [
 *      // License key for Bitrix
 *      'licenseKey' => 'NFR-123-456',
 * 
 *      // Modules to be installed.
 *      // Warning: install the modules using DB migration. Install the modules 
 *      // using the settings of the environment, only for dev environment.
 *      'modules' => [
 *          'vendor.debug'
 *      ],
 * 
 *      // Options for modules 
 *      'options' => [
 *          'vendor.module' => [
 *              'OPTION_CODE' => 'value',
 *              'OPTION_CODE' => ['value' => 'test', 'siteId' => 's1']
 *          ],
 *      ],
 * 
 *      // Settings for module "cluster"
 *      'cluster' => [
 *          'memcache' => [
 *              [
 *                  'GROUP_ID' => 1,
 *                  'HOST' => 'host',
 *                  'PORT' => 'port',
 *                  'WEIGHT' => 'weight',
 *                  'STATUS' => 'status',
 *              ],
 *              [
 *                  'GROUP_ID' => 1,
 *                  'HOST' => 'host',
 *                  'PORT' => 'port',
 *                  'WEIGHT' => 'weight',
 *                  'STATUS' => 'status',
 *              ]
 *          ]
 *      ],
 * 
 *      // Values for file .settings.php
 *      'settings' => [
 *          'connections' => [
 *              'default' => [
 *                  'host' => 'host',
 *                  'database' => 'db',
 *                  'login' => 'login',
 *                  'password' => 'pass',
 *                  'className' => '\\Bitrix\\Main\\DB\\MysqlConnection',
 *                  'options' => 2,
 *              ],
 *          ]
 *      ]
 * ];
 * ```
 */


return [
    // License key for Bitrix
    'licenseKey' => 'DEMO',
    'options' => [
        'main' => [
            'site_name' => 'Имя сайта',
            'server_name' => 'site_url',
            'error_reporting' => 0,
            'optimize_css_files' => 'Y',
            'optimize_js_files' => 'Y',
            'use_minified_assets' => 'Y',
            'move_js_to_body' => 'Y',
            'compres_css_js_files' => 'Y',
            'map_top_menu_type' => 'top',
            'map_left_menu_type' => 'left',
        ],
        'update' => [
            'update_devsrv' => 'N',
        ],
        'event_log' => [
            "event_log_logout" => 'Y',
            "event_log_login_success" => 'Y',
            "event_log_login_fail" => 'Y',
            "event_log_register" => 'Y',
            "event_log_register_fail" => 'Y',
            "event_log_password_request" => 'Y',
            "event_log_password_change" => 'Y',
            "event_log_user_edit" => 'Y',
            "event_log_user_delete" => 'Y',
            "event_log_user_groups" => 'Y',
            "event_log_group_policy" => 'Y',
            "event_log_module_access" => 'Y',
            "event_log_file_access" => 'Y',
            "event_log_task" => 'Y',
            "event_log_marketplace" => 'Y',
        ]
    ],
];