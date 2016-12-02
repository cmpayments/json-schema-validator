## Schema Validator

### Todo
- write some tests in DataTest (look for @TODO)
- write some tests in SchemaTest (look for @TODO)
- update readme.md with changes from v0.0.5

### Important

### changelog [v0.1.0] – 02/12/2016
- Implemented and improved float support for the 'Number' type. Credits: @Bas Peters
- (not part of this release specifically but started semantic versioning as of this release because up until now it wasn’t done properly)

### changelog [v0.0.11] – 25/03/2016
- fixed tests
- added _empty.php (for uit tests) to the project
- removed require-dev from composer.json all together
- added travis.yml to update composer itself before executing tests
- fixed regex (in regexTrait.php) validation where HHVM and PHP output different kind of error messages
- add workaround 'ExceptionClassNameA::getClassName()' to every Exception since ExceptionClassName::class is invalid in PHP5.4
- updated README.md batches
- fixed many scrutinizer remarks

### changelog [v0.0.10] – 24/03/2016
- added the format 'URL', expected format must compliant with RFC2396 with the addition that the value must contain a valid scheme and a valid host

### changelog [v0.0.9] – 03/03/2016
- fixed an issue where when dealing with an array with no items no check was performed on the amount of items even though a "minItems" property was set

### changelog [v0.0.8] – 02/03/2016
- changed the message upon failing a regex test. not outputting the regex anymore, new message reads; The value '%s' of property '%s' is not valid
- in helpers.php, changed comment for method convert_exception_to_array(\Exception $e)
- added method convert_exception_to_array() in duplicate, one for < PHP 7 and one for >= PHP7
- fixed where if the required property for a schema was set but was left empty it would result in an error

### changelog [v0.0.7] – 25/02/2016
- fixed typo in all versions of the use BaseException statement

### changelog [v0.0.6] – 24/02/2016
- updated tests, removed test where a invalid schema would result in an error, as of today an invalid schema results in an exception

### changelog [v0.0.5] – 24/02/2016
- decoupled schema validation from ValidateException and moved to a newly created ValidateSchemaException
- when the schema is not a valid schema it will result in an exception, this used to be an entry in the response in JSON->validate()
- changed output of JSON->validate(), response is now an array with two keys, 'errors', 'warnings'
-- 'errors' contains all things wrong with the user input
-- 'warnings' contains all things related to misconfiguration but are not exception worthy (currently one warning can be triggered, that being the cache directory is not writable)

### changelog [v0.0.4] – 23/02/2016
- added (and improvement) a regex trait
- added documentation to all files
