<?php

namespace Drupal\Tests\entity_clone\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;

/**
 * Create a user and test a clone.
 *
 * @group entity_clone
 */
class EntityCloneUserTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['entity_clone', 'user'];

  /**
   * Theme to enable by default
   * @var string
   */
  protected $defaultTheme = 'classy';

  /**
   * Permissions to grant admin user.
   *
   * @var array
   */
  protected $permissions = [
    'clone user entity',
    'administer users',
  ];

  /**
   * An administrative user with permission to configure users settings.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Sets the test up.
   */
  protected function setUp(): void {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser($this->permissions, 'test_user');
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test user entity clone.
   */
  public function testUserEntityClone() {
    $this->drupalPostForm('entity_clone/user/' . $this->adminUser->id(), [], t('Clone'));

    $users = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties([
        'name' => 'test_user_cloned',
      ]);
    $user = reset($users);
    $this->assertInstanceOf(User::class, $user, 'Test user cloned found in database.');
  }

}
