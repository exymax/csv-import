# data-import
**A Symfony2 console application for importing database source files(csv is ready for now) into a database**

To use it in your application open terminal and type

`php app/console app:data-import <options> <filepath>`



- `--test-mode`, or `-test` if you want just to process your file, not import. It will log results into console like you launched the command in normal mode.
 
- `filepath` is a path to your .csv file. 