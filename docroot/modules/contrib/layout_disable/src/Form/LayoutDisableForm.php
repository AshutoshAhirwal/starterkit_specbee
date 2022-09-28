<?php

namespace Drupal\layout_disable\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Layout\LayoutPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Administration settings form.
 */
class LayoutDisableForm extends ConfigFormBase {

  /**
   * A date formatter object.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManager
   */
  protected $layoutPluginManager;

  /**
   * Class constructor.
   */
  public function __construct(LayoutPluginManager $layoutPluginManager) {
    $this->layoutPluginManager = $layoutPluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.core.layout'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_disable';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = $this->config('layout_disable.settings');

    $layouts = $this->layoutPluginManager
      ->getSortedDefinitions();

    $layout_names = [];
    if (!empty($layouts)) {
      foreach ($layouts as $layoutName => $layoutDefinition) {
        if ($layoutName == 'layout_onecol') {
          // layout_onecol is required by core and can not be disabled!
          continue;
        }
        $layout_names[Html::escape($layoutName)] = Html::escape($layoutDefinition->getLabel()) . ' (' . Html::escape($layoutDefinition->id()) . ')';
      }
    }

    // Already disabled layouts have already been removed here because the
    // form is built after layout_disable_layout_alter. See
    // #2983016 (https://www.drupal.org/project/layout_disable/issues/2983016)
    // So we have to make them available manually:
    $disabled_layouts = $settings->get('disabled_layouts');
    if (!empty($disabled_layouts)) {
      $this->messenger()
        ->addWarning('If already removed from code (disabled) layouts appear here, uncheck them to remove them finally. They are still listed due to a hook_layout_alter logical incompatibility.');
      // @todo This may lead to listing already uninstalled layouts here. Find a way to only show existing layouts despite alteration.
      $layout_names = array_merge($disabled_layouts, $layout_names);
    }

    $form['disabled_layouts'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Disable layouts'),
      '#description' => $this->t('Select the layouts you wish to disable. "layout_onecol" is required by core and can not be disabled!'),
      '#default_value' => !empty($disabled_layouts) ? $disabled_layouts : [],
      '#options' => $layout_names,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('layout_disable.settings');
    $form_values = $form_state->getValues();

    if (!empty($form_values)) {
      foreach ($form_values['disabled_layouts'] as $disabledLayout => $layoutStatus) {
        if (!$layoutStatus) {
          // Only save disabled layouts (status =1) to keep the lsit as
          // small as possible.
          unset($form_values['disabled_layouts'][$disabledLayout]);
        }
      }
    }

    $config->set('disabled_layouts', $form_values['disabled_layouts'])
      ->save();
    parent::submitForm($form, $form_state);

    // Clear layout caches:
    $this->layoutPluginManager->clearCachedDefinitions();
  }

}
