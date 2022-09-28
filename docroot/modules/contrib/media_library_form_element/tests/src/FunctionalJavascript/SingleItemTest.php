<?php

namespace Drupal\Tests\media_library_form_element\FunctionalJavascript;

use Drupal\file\Entity\File;
use Drupal\media\Entity\MediaType;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\media_library\FunctionalJavascript\MediaLibraryTestBase;

/**
 * Test using the media library element.
 *
 * @group media_library
 */
class SingleItemTest extends MediaLibraryTestBase {

  use TestFileCreationTrait;

  /**
   * The test fixtures to create.
   *
   * @var array
   */
  protected const FIXTURES = [
    'type_one' => [
      'Horse',
      'Bear',
      'Cat',
      'Dog',
    ],
    'type_two' => [
      'Crocodile',
      'Lizard',
      'Snake',
      'Turtle',
    ],
    'type_three' => [
      '1',
      '2',
      '3',
    ],
  ];

  /**
   * The modules to load to run the test.
   *
   * @var array
   */
  protected static $modules = [
    'media_library',
    'media_library_test',
    'media_library_form_element',
    'media_library_form_element_test',
  ];

  /**
   * Specify the theme to be used in testing.
   *
   * @var string
   */
  protected $defaultTheme = 'classy';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    // Bypass the need in the test module to define schema.
    $this->strictConfigSchema = NULL;

    parent::setUp();

    File::create([
      'filename' => 'duck.png',
      'uri' => 'public://duck.png',
      'filemime' => 'image/png',
      'status' => 1,
    ])->save();

    File::create([
      'filename' => 'platypus.png',
      'uri' => 'public://platypus.png',
      'filemime' => 'image/png',
      'status' => 1,
    ])->save();

    File::create([
      'filename' => 'goose.png',
      'uri' => 'public://goose.png',
      'filemime' => 'image/png',
      'status' => 1,
    ])->save();

    $this->createMediaItems(static::FIXTURES);

    // Create a user that can only add media of type one.
    $user = $this->drupalCreateUser([
      'access administration pages',
      'access content',
      'create type_one media',
      'view media',
    ]);
    $this->drupalLogin($user);
  }

  /**
   * Asserts that only allowed entities are listed in the widget.
   *
   * @param string $selector_type
   *   The css selector of the media library form element to test.
   * @param string $selector
   *   The css selector of the media library form element to test.
   * @param array $allowed_bundles
   *   The bundles that are allowed for the element to test.
   */
  protected function assertAllowedBundles($selector_type, $selector, array $allowed_bundles) {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Open the media library.
    $assert->elementExists($selector_type, $selector)->press();

    // Wait for the media library to open.
    $assert->assertWaitOnAjaxRequest();

    // Make sure that the bundle menu works as intended.
    if (count($allowed_bundles) === 1) {

      // If a single bundle is allowed, the menu shouldn't be displayed.
      $assert->elementNotExists('css', '.media-library-menu');
    }
    else {

      // Make sure that the proper menu items appear.
      foreach (static::FIXTURES as $bundle => $entities) {

        $media_type = MediaType::load($bundle);
        $media_type_label = $media_type->label();
        if (in_array($bundle, $allowed_bundles, TRUE)) {

          // If the bundle is allowed, it should be contained in the menu.
          $assert->elementTextContains('css', '.media-library-menu', $media_type_label);

          // Switch to the proper bundle.
          $page->clickLink($media_type_label);

          // Wait for the new entities to load in.
          $assert->assertWaitOnAjaxRequest();

          // Make sure all the entities appear.
          foreach ($entities as $entity) {
            $assert->linkExists($entity);
          }
        }
        else {

          // If the bundle is not allowed, it should not be contained in the menu.
          $assert->elementNotContains('css', '.media-library-menu', $media_type_label);

          // If the bundle is not allowed, make sure none of the entities appear.
          foreach ($entities as $entity) {
            $assert->linkNotExists($entity);
          }
        }
      }
    }

    // Close out of the media library.
    $assert->elementExists('css', '.ui-dialog-titlebar-close')->press();
  }

  /**
   * Asserts that the media library preview contains the provided items.
   *
   * @param array $items
   *   The items to check for.
   */
  protected function assertPreviewContains(array $items) {
    $assert = $this->assertSession();

    foreach ($items as $index => $item) {
      $nth = $index + 1;
      $selector = ".media-library-item:nth-of-type($nth) .media-library-item__name";
      $assert->elementContains('css', $selector, $item);
    }
  }

  /**
   * @param string $selector_type
   *   The css selector of the media library form element to test.
   * @param string $selector
   *   The css selector of the media library form element to test.
   * @param string $bundle
   *   The bundle of the media item to insert.
   * @param int $index
   *   The index of the media item to insert.
   */
  protected function insertMediaItem($selector_type, $selector, $bundle, $index) {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();

    $media_type = MediaType::load($bundle);
    $media_type_label = $media_type->label();

    // Open the media library.
    $assert->elementExists($selector_type, $selector)->press();

    // Wait for the media library to open.
    $assert->assertWaitOnAjaxRequest();

    // Select the proper bundle from the menu (if it exists).
    if ($page->hasLink($media_type_label)) {
      $page->clickLink($media_type_label);
      $assert->assertWaitOnAjaxRequest();
    }

    // Select the item.
    $page->find('css', 'input[name="media_library_select_form[' . $index . ']"]')->setValue('1');
    $assert->assertWaitOnAjaxRequest();

    $assert->checkboxChecked('media_library_select_form[' . $index . ']');

    // Insert the item.
    $insert_button = $page->find('css', '.ui-dialog-buttonset .form-submit');
    $insert_button->press();

    $assert->assertWaitOnAjaxRequest();
  }

  /**
   * Tests the setting form.
   */
  public function testForm() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalGet('media-library-form-element-test-form');

    /*************************************************/
    /* Test for the single cardinality form element. */
    /*************************************************/

    // Check the initial element state.
    $assert->elementContains('css', '#media_single-media-library-wrapper--description', 'Upload or select your profile image');
    $assert->elementContains('css', '#media_single-media-library-wrapper--description', 'One media item remaining');

    // Check that only configured bundles are allowed.
    $this->assertAllowedBundles('css', '#edit-media-single-media-library-open-button', ['type_one', 'type_two']);

    // Insert an item and assert that the state updates appropriately.
    $this->insertMediaItem('css', '#edit-media-single-media-library-open-button', 'type_one', 0);
    $this->assertPreviewContains(['Dog']);
    $assert->elementContains('css', '#media_single-media-library-wrapper--description', 'The maximum number of media items have been selected.');

    // Save the form and assert that the selection is persisted.
    $page->pressButton('Save configuration');
    $this->assertPreviewContains(['Dog']);
    $assert->elementContains('css', '#media_single-media-library-wrapper--description', 'The maximum number of media items have been selected.');

    // Remove all selected items.
    $page->pressButton('Remove');
    $this->waitForNoText('Dog');
    $page->pressButton('Save configuration');

    // Check that the form element is reset to its initial state.
    $assert->pageTextNotContains('Dog');
    $assert->elementContains('css', '#media_single-media-library-wrapper--description', 'One media item remaining');

    /*************************************************/
    /* Test for the single cardinality form element. */
    /*************************************************/

    // Check the initial element state.
    $assert->elementContains('css', '#media_multiple-media-library-wrapper--description', 'Upload or select multiple images');
    $assert->elementContains('css', '#media_multiple-media-library-wrapper--description', '2 media items remaining');

    // Check that only configured bundles are allowed.
    $this->assertAllowedBundles('css', '#edit-media-multiple-media-library-open-button', ['type_one']);

    // Insert an item and assert that the state updates appropriately.
    $this->insertMediaItem('css', '#edit-media-multiple-media-library-open-button', 'type_one', 0);
    $this->assertPreviewContains(['Dog']);
    $assert->elementContains('css', '#media_multiple-media-library-wrapper--description', 'One media item remaining');

    // Insert a second item and assert that hte state updates appropriately.
    $this->insertMediaItem('css', '[id^="edit-media-multiple-media-library-open-button"]', 'type_one', 1);
    $this->assertPreviewContains(['Dog', 'Cat']);
    $assert->elementContains('css', '#media_multiple-media-library-wrapper--description', 'The maximum number of media items have been selected.');

    // Remove all of the items.
    foreach (['Dog', 'Cat'] as $item) {
      $page->pressButton('Remove');
      $this->waitForNoText($item);
      $page->pressButton('Save configuration');

      // Check that the item was removed.
      $assert->pageTextNotContains($item);
    }

    // Check that the form element is reset to its initial state.
    $assert->elementContains('css', '#media_multiple-media-library-wrapper--description', 'Upload or select multiple images');

    /****************************************************/
    /* Test for the unlimited cardinality form element. */
    /****************************************************/

    // Check the initial element state.
    $assert->elementTextContains('css', '#media_unlimited-media-library-wrapper--description', 'Upload or select unlimited images.');

    // Check that only configured bundles are allowed.
    $this->assertAllowedBundles('css', '#edit-media-unlimited-media-library-open-button', ['type_two']);

    // Insert an item and assert that the state updates appropriately.
    $this->insertMediaItem('css', '#edit-media-unlimited-media-library-open-button', 'type_two', 0);
    $this->assertPreviewContains(['Turtle']);
    $assert->elementTextContains('css', '#media_unlimited-media-library-wrapper--description', 'Upload or select unlimited images.');

    // Insert a second item and assert that the state updates appropriately.
    $this->insertMediaItem('css', '[id^="edit-media-unlimited-media-library-open-button"]', 'type_two', 1);
    $this->assertPreviewContains(['Turtle', 'Snake']);
    $assert->elementTextContains('css', '#media_unlimited-media-library-wrapper--description', 'Upload or select unlimited images.');

    // Insert a third item and assert that the state updates appropriately.
    $this->insertMediaItem('css', '[id^="edit-media-unlimited-media-library-open-button"]', 'type_two', 2);
    $this->assertPreviewContains(['Turtle', 'Snake', 'Lizard']);
    $assert->elementTextContains('css', '#media_unlimited-media-library-wrapper--description', 'Upload or select unlimited images.');

    // Insert a fourth item and assert that the state updates appropriately.
    $this->insertMediaItem('css', '[id^="edit-media-unlimited-media-library-open-button"]', 'type_two', 3);
    $this->assertPreviewContains(['Turtle', 'Snake', 'Lizard', 'Crocodile']);
    $assert->elementTextContains('css', '#media_unlimited-media-library-wrapper--description', 'Upload or select unlimited images.');

    // Remove all of the items.
    foreach (['Turtle', 'Snake', 'Lizard', 'Crocodile'] as $item) {
      $page->pressButton('Remove');
      $this->waitForNoText($item);
      $page->pressButton('Save configuration');

      // Check that the item was removed.
      $assert->pageTextNotContains($item);
    }

    // Check that the form element is reset to its initial state.
    $assert->elementContains('css', '#media_unlimited-media-library-wrapper--description', 'Upload or select unlimited images');

    /*******************************************************/
    /* Test for when a referenced media entity is deleted. */
    /*******************************************************/

    // Add a bunch of media entities.
    $this->insertMediaItem('css', '#edit-media-unlimited-media-library-open-button', 'type_two', 0);
    $this->insertMediaItem('css', '[id^="edit-media-unlimited-media-library-open-button"]', 'type_two', 1);
    $this->insertMediaItem('css', '[id^="edit-media-unlimited-media-library-open-button"]', 'type_two', 2);
    $this->insertMediaItem('css', '[id^="edit-media-unlimited-media-library-open-button"]', 'type_two', 3);

    // Save the configuration.
    $page->pressButton('Save configuration');


    // Delete a couple media entities that are referenced above.
    \Drupal::entityTypeManager()->getStorage('media')->load(5)->delete();
    \Drupal::entityTypeManager()->getStorage('media')->load(6)->delete();

    // Ensure that there is not a WSOD.
    $this->drupalGet('media-library-form-element-test-form');
    $assert->pageTextContains('Test Form');
  }

  /**
   * Tests webform.
   */
  public function testWebform() {
      $assert = $this->assertSession();
      $page = $this->getSession()->getPage();
      $this->drupalGet('form/media-element');

      $assert->elementContains('css', '#test-media-library-wrapper--description', 'One media item remaining');

      $page->pressButton('Add media');
      $assert->assertWaitOnAjaxRequest();
      $assert->elementContains('css', '.media-library-menu a', 'Type One');
      $assert->pageTextContains('Type Two');
      $assert->pageTextNotContains('Type Three');

      $assert->pageTextContains('Horse');
      $assert->pageTextContains('Bear');

      $page->find('css', 'input[name="media_library_select_form[0]"]')->setValue('1');
      $assert->assertWaitOnAjaxRequest();
      $assert->checkboxChecked('media_library_select_form[0]');

      $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
      $assert->assertWaitOnAjaxRequest();

      $assert->elementContains('css', '.media-library-item__name', 'Dog');
      $assert->elementContains('css', '#test-media-library-wrapper--description', 'The maximum number of media items have been selected.');

      $assert->pageTextContains('Dog');
      $assert->elementContains('css', '.media-library-item__name', 'Dog');

      $page->pressButton('Remove');
      $this->waitForNoText('Dog');
      $assert->pageTextNotContains('Dog');
      $assert->elementContains('css', '#test-media-library-wrapper--description', 'One media item remaining');
    }

}
