# csv-import
**A Symfony2 console application for importing csv files into a database**

To use it in your application open terminal and type

`php app/console app:csv-import <options> <filepath>`

**csv-import** supports the following `options`:

- `--test-mode`, or `-test` if you want just to process .csv, not import. It will log results into console like you launched the command in normal mode.

- `--log-field` or `-field` is an optional parameter if you want to specify the field, which used to log results.
Default value is `code`. For example: `--log-field=cost`.
 
 `filepath` is a path to your .csv file. 