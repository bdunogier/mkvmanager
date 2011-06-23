<?php
/**
 * Persistent object definition for mm\Daemon\QueueItem
 */
$def = new ezcPersistentObjectDefinition();
$def->table = 'operations_queue';
$def->class = 'mm\Daemon\QueueItem';

$def->idProperty = new ezcPersistentObjectIdProperty;
$def->idProperty->columnName = 'hash';
$def->idProperty->propertyName = 'hash';
$def->idProperty->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;
$def->idProperty->generator = new ezcPersistentGeneratorDefinition( 'ezcPersistentManualGenerator' );

$def->properties['type'] = new ezcPersistentObjectProperty;
$def->properties['type']->columnName = 'type';
$def->properties['type']->propertyName = 'type';
$def->properties['type']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;

$def->properties['title'] = new ezcPersistentObjectProperty;
$def->properties['title']->columnName = 'title';
$def->properties['title']->propertyName = 'title';
$def->properties['title']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;

$def->properties['createTime'] = new ezcPersistentObjectProperty;
$def->properties['createTime']->columnName = 'create_time';
$def->properties['createTime']->propertyName = 'createTime';
$def->properties['createTime']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['startTime'] = new ezcPersistentObjectProperty;
$def->properties['startTime']->columnName = 'start_time';
$def->properties['startTime']->propertyName = 'startTime';
$def->properties['startTime']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['endTime'] = new ezcPersistentObjectProperty;
$def->properties['endTime']->columnName = 'end_time';
$def->properties['endTime']->propertyName = 'endTime';
$def->properties['endTime']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['objectString'] = new ezcPersistentObjectProperty;
$def->properties['objectString']->columnName = 'object_string';
$def->properties['objectString']->propertyName = 'objectString';
$def->properties['objectString']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;

$def->properties['status'] = new ezcPersistentObjectProperty;
$def->properties['status']->columnName = 'status';
$def->properties['status']->propertyName = 'status';
$def->properties['status']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['pid'] = new ezcPersistentObjectProperty;
$def->properties['pid']->columnName = 'pid';
$def->properties['pid']->propertyName = 'pid';
$def->properties['pid']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['message'] = new ezcPersistentObjectProperty;
$def->properties['message']->columnName = 'message';
$def->properties['message']->propertyName = 'message';
$def->properties['message']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;

$def->properties['progress'] = new ezcPersistentObjectProperty;
$def->properties['progress']->columnName = 'progress';
$def->properties['progress']->propertyName = 'progress';
$def->properties['progress']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

return $def;
?>