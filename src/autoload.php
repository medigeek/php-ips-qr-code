phpab 6c6a741-dirty - Copyright (C) 2009 - 2020 by Arne Blankerts and Contributors

Scanning directory src

<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'medigeek\\ipsqrcodeobject' => '/IPSQRCodeObject.php',
                'medigeek\\ipsqrcodeparser' => '/IPSQRCodeParser.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    },
    true,
    false
);
// @codeCoverageIgnoreEnd


