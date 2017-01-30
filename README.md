# csv-import
**A Symfony2 console application for importing csv files into a database**

To use it in your application open terminal and type

`php app/console app:csv-import <options> <filepath>`



- `--test-mode`, or `-test` if you want just to process .csv, not import. It will log results into console like you launched the command in normal mode.
 
- `filepath` is a path to your .csv file. 