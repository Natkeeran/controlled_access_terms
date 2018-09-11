<?php

namespace Drupal\Tests\controlled_access_terms\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Integration test for the field configuration form.
 *
 * @group controlled_access_terms
 */
class FieldConfigurationTest extends BrowserTestBase {
  protected $profile = 'standard';
  protected $strictConfigSchema = FALSE;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'field_ui',
    'node',
    'controlled_access_terms',
    'controlled_access_terms_default_configuration',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->drupalLogin($this->rootUser);

    // Setup content type with required field
    $newType = $this->createContentType(['type' => 'islandora_object', 'name' => 'islandora_object']);
    drupal_flush_all_caches();
    $this->drupalGet('admin/structure/types/manage/islandora_object/fields/add-field');
    $this->submitForm([
      'new_storage_type' => 'typed_relation',
      'label' => 'Typed Person',
      'field_name' => 'typed_person',
    ], t('Save and continue'));

    $this->submitForm([
      'settings[target_type]' => 'taxonomy_term',
    ], t('Save field settings'));

    $relators = "relators:anl|Analyst (anl)" . PHP_EOL;
    $relators .= "relators:anm|Animator (anm)";

    $this->submitForm([
      'label' => 'Typed Person',
      'description' => 'Some help.',
      'required' => '0',
      'settings[handler_settings][target_bundles][person]' => 'person',
      'settings[rel_types]' => $relators,
    ], t('Save settings'));

    // Setup taxonomy
    $this->drupalGet('admin/structure/taxonomy/manage/person/add');
    $this->submitForm([
      'name[0][value]' => 'Mohandas Karamchand Gandhi',
      'field_person_name_authorities[0][source]' => 'other',
      'field_person_name_authorities[0][uri]' => 'https://www.wikidata.org/wiki/Q1001',
      'field_person_name_authorities[0][title]' => 'Wikidata',
      'field_person_preferred_name[0][given]' => 'Mahatma Gandhi',
    ], t('Save'));
  }

  /**
   * Test that the typed_relation field has been added to the content type.
   */
  public function testFieldConfiguration() {
    $this->drupalGet('admin/structure/types/manage/islandora_object/fields');
    $this->assertSession()->pageTextContains('field_typed_person');
  }

  /**
   * Test that the a person term has been added to person taxonomy.
   */
  public function testAddPersonTerm() {
    $this->drupalGet('admin/structure/taxonomy/manage/person/overview');
    $this->assertSession()->pageTextContains('Mohandas Karamchand Gandhi');
  }

  /**
   * Test that one can create a node with person relation and target.
   */
  public function testNodeWithPersonTypedRelation() {
    $this->drupalGet('node/add/islandora_object');
    $this->submitForm([
      'title[0][value]' => 'test_person_field',
      'field_typed_person[0][rel_type]' => 'relators:anl',
      'field_typed_person[0][target_id]' => 'Mohandas Karamchand Gandhi',
    ], t('Save'));

    $this->assertSession()->pageTextContains('test_person_field has been created.');
  }
}
