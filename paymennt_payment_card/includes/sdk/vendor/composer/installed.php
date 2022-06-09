<?php return array(
    'root' => array(
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => NULL,
        'name' => 'paymennt/paymennt-php-test',
        'dev' => true,
    ),
    'versions' => array(
        'paymennt/paymennt-php' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'type' => 'library',
            'install_path' => __DIR__ . '/../paymennt/paymennt-php',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'reference' => 'ecec910af72c42fefe2f2ed8ad09c190b0ee4b14',
            'dev_requirement' => false,
        ),
        'paymennt/paymennt-php-test' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => NULL,
            'dev_requirement' => false,
        ),
    ),
);
