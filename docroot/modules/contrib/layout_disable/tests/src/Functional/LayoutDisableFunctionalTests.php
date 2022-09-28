<?php

namespace Drupal\Tests\layout_disable\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * This class provides methods specifically for testing something.
 *
 * @group layout_disable
 */
class LayoutDisableFunctionalTests extends BrowserTestBase {
  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_disable',
    'test_page_test',
    'node',
    'layout_discovery',
    'layout_builder',
  ];

  /**
   * A user with authenticated permissions.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * A user with admin permissions.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->config('system.site')->set('page.front', '/test-page')->save();
    $this->user = $this->drupalCreateUser([]);
    $this->adminUser = $this->drupalCreateUser([]);
    $this->adminUser->addRole($this->createAdminRole('admin', 'admin'));
    $this->adminUser->save();
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests if the module installation, won't break the site.
   */
  public function testInstallation() {
    $session = $this->assertSession();
    $this->drupalGet('<front>');
    $session->statusCodeEquals(200);
  }

  /**
   * Tests "access layout_disable" permission.
   */
  public function testAccess() {
    $session = $this->assertSession();
    // Check access as admin:
    $this->drupalGet('/admin/config/user-interface/layout-disable');
    $session->statusCodeEquals(200);
    $this->drupalLogout();
    // Check access as user with correct permissions:
    $this->drupalLogin($this->drupalCreateUser(['access layout_disable']));
    $this->drupalGet('/admin/config/user-interface/layout-disable');
    $session->statusCodeEquals(200);
    $this->drupalLogout();
    // Check access as user without permissions:
    $this->drupalLogin($this->user);
    $this->drupalGet('/admin/config/user-interface/layout-disable');
    $session->statusCodeEquals(403);
    $this->drupalLogout();
    // Check access as anonymous:
    $this->drupalGet('/admin/config/user-interface/layout-disable');
    $session->statusCodeEquals(403);
  }

  /**
   * Tests disabling a layout.
   */
  public function testDisableEnableLayout() {
    $session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Create a Content and enable layout_onecol:
    $this->createContentType(['type' => 'article']);
    $this->drupalGet('/admin/structure/types/manage/article/display');
    $session->statusCodeEquals(200);
    $page->fillField('edit-layout-enabled', 1);
    $page->pressButton('edit-submit');
    $session->statusCodeEquals(200);
    // See if we can set the layout as display with layout_builder:
    $this->drupalGet('/layout_builder/choose/section/defaults/node.article.default/0');
    $session->statusCodeEquals(200);
    $session->pageTextContains('Two column');
    $this->drupalGet('/layout_builder/configure/section/defaults/node.article.default/0/layout_twocol_section');
    $session->statusCodeEquals(200);
    // Disable the layout on the layout_disable page:
    $this->drupalGet('/admin/config/user-interface/layout-disable');
    $session->statusCodeEquals(200);
    $page->fillField('edit-disabled-layouts-layout-twocol', TRUE);
    $page->fillField('edit-disabled-layouts-layout-twocol-section', TRUE);
    $page->pressButton('edit-submit');
    $session->statusCodeEquals(200);
    $session->pageTextContains('The configuration options have been saved.');
    // See if the layout is gone:
    $this->drupalGet('/layout_builder/choose/section/defaults/node.article.default/0');
    $session->statusCodeEquals(200);
    $session->pageTextNotContains('Two column');
    $this->drupalGet('/layout_builder/configure/section/defaults/node.article.default/0/layout_twocol_section');
    $session->statusCodeEquals(500);
    $session->pageTextContains('The website encountered an unexpected error. Please try again later.');
    // Reenable the layouts again, and see if they show up again:
    $this->drupalGet('/admin/config/user-interface/layout-disable');
    $session->statusCodeEquals(200);
    $page->fillField('edit-disabled-layouts-layout-twocol', FALSE);
    $page->fillField('edit-disabled-layouts-layout-twocol-section', FALSE);
    $page->pressButton('edit-submit');
    $session->statusCodeEquals(200);
    $session->pageTextContains('The configuration options have been saved.');
    $this->drupalGet('/layout_builder/choose/section/defaults/node.article.default/0');
    $session->statusCodeEquals(200);
    $session->pageTextContains('Two column');
    $this->drupalGet('/layout_builder/configure/section/defaults/node.article.default/0/layout_twocol_section');
    $session->statusCodeEquals(200);
  }

}
