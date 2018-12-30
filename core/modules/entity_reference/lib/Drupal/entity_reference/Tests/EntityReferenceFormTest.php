<?php

/**
 * @file
 * Definition of Drupal\entity_reference\Tests\EntityReferenceFormTest
 */

namespace Drupal\entity_reference\Tests;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the forms that use an entity reference field type.
 */
class EntityReferenceFormTest extends WebTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Entity reference form',
      'description' => 'Tests entity references used on forms.',
      'group' => 'Entity Reference',
    );
  }

  public static $modules = array('node', 'entity_reference', 'entity_test', 'field', 'field_ui', 'options');

  function setUp() {
    parent::setUp();

    // Create an Article node type.
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));
    // Create an administrator.
    $this->admin_user = $this->drupalCreateUser(array('administer content types', 'administer node fields', 'administer node display', 'administer nodes', 'bypass node access'));
    $this->drupalLogin($this->admin_user);
  }

  /**
   * Create, edit and delete a entity_reference field via the user interface.
   */
  function testEntityReferenceFormField() {
    // Visit the article field administration page.
    $this->drupalGet('admin/structure/types/manage/article/fields');
    $this->assertResponse(200, 'Manage fields page accessed.');

    // Check for a entity_reference field.
    $this->assertNoText('Entity Reference test field', 'Entity Reference form field not found.');

    // Visit the article field display administration page.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertResponse(200, 'Manage field display page accessed.');

    // Check for a entity_reference field.
    $this->assertNoText('Entity Reference test field', 'Entity Reference form field not found.');

    // Create test entity_reference field.
    $this->entityReferenceCreateTestField();

    // Visit the article field administration page.
    $this->drupalGet('admin/structure/types/manage/article/fields');

    // Check the new field.
    $this->assertText('Entity Reference test field', 'Added a test field instance.');

    // Visit the article field display administration page.
    $this->drupalGet('admin/structure/types/manage/article/display');

    // Check the new field.
    $this->assertText('Entity Reference test field', 'Added a test field display instance.');
  }

  /**
   * Create content for a entity_reference field.
   */
  function testEntityReferenceFieldStorage() {
    // Create test entity_reference field.
    $this->entityReferenceCreateTestField();

    // Create five entity_test entities.
    foreach (array('one', 'two', 'three', 'four', 'five') as $name) {
      $entity = entity_create('entity_test', array('name' => $name));
      $entity->save();
      $entities[] = $entity;
    }

    // Visit the article field display administration page.
    $this->drupalGet('node/add/article');
    $this->assertResponse(200);

    // Check the new field exists on the page.
    $this->assertText('Entity Reference test field', 'Found the entity_reference field instance.');

    // We expect to find 5 entity_reference options.
    foreach ($entities as $entity_reference) {
      $string = 'value="' . $entity_reference->id() . '"';
      $this->assertRaw($string, format_string('Found the %entity_reference option.', array('%entity_reference' => $entity_reference->label())));
      if (!isset($one)) {
        $one = $entity_reference->id();
        continue;
      }
      if (!isset($two)) {
        $two = $entity_reference->id();
      }
    }

    // Try to post a node, assigned to the first two entity_references.
    $edit["title[0][value]"] = 'Test node';
    $edit["field_entity_reference[{$one}]"] = TRUE;
    $edit["field_entity_reference[{$two}]"] = TRUE;
    $this->drupalPostForm('node/add/article', $edit, 'Save and publish');
    $this->assertResponse(200);
    $node = node_load(1);
    $values = $node->getPropertyValues();
    // @TODO watch for changes in core that affect this test.
    $this->assertTrue(count($values['field_entity_reference']) == 2, 'Node saved with two entity_reference records.');

  }

  /**
   * Creates a simple field for testing on the article content type.
   */
  function entityReferenceCreateTestField() {
    $label = 'entity_reference';
    $name = 'field_' . $label;

    $settings = array(
      'name' => $name,
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'cardinality' => -1,
      'settings' => array(
        'target_type' => 'entity_test',
      ),
    );
    $field = entity_create('field_entity', $settings);
    $field->save();

    $instance = array(
      'field_name' => $name,
      'entity_type' => 'node',
      'label' => 'Entity Reference test field',
      'bundle' => 'article',
    );
    $field_instance = entity_create('field_instance', $instance);
    $field_instance->save();

    // @TODO: Loop through the options to try each type.
    // Tell the form system how to behave.
    entity_get_form_display('node', 'article', 'default')
      ->setComponent($name, array(
        'type' => 'options_buttons',
    ))
    ->save();
  }

}
