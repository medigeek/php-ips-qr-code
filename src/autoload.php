phpab 1.25.9 - Copyright (C) 2009 - 2020 by Arne Blankerts and Contributors

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
                'MediGeek\\IPSQRCodeObject' => '/IPSQRCodeObject.php',
                'MediGeek\\IPSQRCodeParser' => '/IPSQRCodeParser.php'
            );
        }
        if (isset($classes[$class])) {
            require __DIR__ . $classes[$class];
        }
    },
    true,
    false
);
// @codeCoverageIgnoreEnd


