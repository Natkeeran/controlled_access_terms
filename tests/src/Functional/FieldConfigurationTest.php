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
  }

  /**
   * Test the field configuration form.
   */
  public function testFieldConfiguration() {

    $this->createContentType(['type' => 'islandora_object', 'name' => 'islandora_object']);
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

    $this->submitForm([
      'label' => 'Typed Person',
      'description' => 'Some help.',
      'required' => '0',
      'settings[handler_settings][target_bundles][person]' => 'person',
      'settings[rel_types]' => 'relators:anl|Analyst (anl)',
    ], t('Save settings'));

    $this->assertSession()->pageTextContains('Saved Typed Person configuration');
  }

}
