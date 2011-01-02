<?php
$def = new ezcPersistentObjectDefinition();
$def->table = "commands";
$def->class = "mmMergeOperation";

$def->idProperty = new ezcPersistentObjectIdProperty;
$def->idProperty->columnName = 'hash';
$def->idProperty->propertyName = 'hash';
$def->idProperty->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;
$def->idProperty->generator = new ezcPersistentGeneratorDefinition( 'ezcPersistentManualGenerator' );

$def->properties['startTime'] = new ezcPersistentObjectProperty;
$def->properties['startTime']->columnName = 'start_time';
$def->properties['startTime']->propertyName = 'startTime';
$def->properties['startTime']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['endTime'] = new ezcPersistentObjectProperty;
$def->properties['endTime']->columnName = 'end_time';
$def->properties['endTime']->propertyName = 'endTime';
$def->properties['endTime']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['command'] = new ezcPersistentObjectProperty;
$def->properties['command']->columnName = 'command';
$def->properties['command']->propertyName = 'command';
$def->properties['command']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;

$def->properties['targetFile'] = new ezcPersistentObjectProperty;
$def->properties['targetFile']->columnName = 'target_file';
$def->properties['targetFile']->propertyName = 'targetFile';
$def->properties['targetFile']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;

$def->properties['targetFileSize'] = new ezcPersistentObjectProperty;
$def->properties['targetFileSize']->columnName = 'target_file_size';
$def->properties['targetFileSize']->propertyName = 'targetFileSize';
$def->properties['targetFileSize']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;

$def->properties['pid'] = new ezcPersistentObjectProperty;
$def->properties['pid']->columnName = 'pid';
$def->properties['pid']->propertyName = 'pid';
$def->properties['pid']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['status'] = new ezcPersistentObjectProperty;
$def->properties['status']->columnName = 'status';
$def->properties['status']->propertyName = 'status';
$def->properties['status']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['message'] = new ezcPersistentObjectProperty;
$def->properties['message']->columnName = 'message';
$def->properties['message']->propertyName = 'message';
$def->properties['message']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;

return $def;