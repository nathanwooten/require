<?php

$entry = [

  'helperPathDefineAbsolute' => [
    [
      'USER_PATH' => dirname( dirname( __FILE__ ) ),
      'PUBLIC_HTML' => dirname( __FILE__ )
    ]
  ],

  'helperPathDefineRelative' => [
    [
      'VENDOR' => [ 'USER_PATH', 'vendor' ],
      'LOCAL' => [ 'USER_PATH', 'main' ]
    ]
  ],

  'helperPathRequireRelative' => [
    [
      [ 'VENDOR', 'autoload.php' ],
      [ 'VENDOR', 'nathanwooten' . DS . 'lib.php' ],
      [ 'LOCAL', 'top.php' ]
    ]
  ]

];

return $entry;