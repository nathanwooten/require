# require
A dependency manager that defines paths and requires files based on those paths.
The manager is the require.php file that is to be included in every directory that has dependencies. It targets the directory structure's closest 'entry.php' which specifies callbacks and arguments for defining paths and including files. Paths and files can be defined with an absolute path, or as a sub-path of an absolute path.

## Usage

// in you index file(s)
```php
<?php

require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'require.php';

// your application ...

```

This works because the require file uses a function called helperPathUpFind which locates the next 'entry.php' (or whatever filename you choose), up in the directory structure. The entry file includes the settings for running the require file. You don't need an entry file in every folder, you only need one in your project's root folder.
