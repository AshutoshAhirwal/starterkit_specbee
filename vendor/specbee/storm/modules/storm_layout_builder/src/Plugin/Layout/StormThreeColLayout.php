<?php

namespace Drupal\storm_layout_builder\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;

/**
 * Configurable two column layout plugin class.
 *
 * @internal
 *   Plugin classes are internal.
 */
class StormThreeColLayout extends StormLayout {

  /**
   * {@inheritdoc}
   */
  protected function getWidthOptions() {
    return [
      '25-50-25' => '25-50-25',
      '33-34-33' => '33-34-33',
      '25-25-50' => '25-25-50',
      '50-25-25' => '50-25-25',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['column_widths'] = [
      '#type' => 'details',
      '#title' => $this->t('Column widths'),
      '#weight' => 2,
    ];
    $form['column_widths']['options'] = [
      '#type' => 'select',
      '#default_value' => $this->configuration['column_widths'],
      '#options' => $this->getWidthOptions(),
      '#description' => $this->t('Choose the column widths for this layout.'),
      '#weight' => 2,
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();
    return $configuration + [
      'column_widths' => '33-34-33',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['column_widths'] = $form_state->getValue('column_widths');
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);
    $build['#attributes']['class'] = [
      'l-grid',
      'l-grid--' . $this->configuration['column_widths']['options'],
    ];
    return $build;
  }

}
