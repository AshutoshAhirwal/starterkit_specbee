<?php

namespace Drupal\storm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Storm settings for this site.
 */
class StormCtaButtonTypeValuesForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'storm_storm_cta_button_type_values';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['storm.cta_button.type.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['allowed_values'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Allowed values'),
      '#description' => $this->t('<p>Enter one value per line, in the format <b>key|label</b> where <em>key</em> is the CSS class name (without the .), and <em>label</em> is the human readable name of the Button type.</p>'),
      '#default_value' => $this->config('storm.cta_button.type.settings')->get('allowed_values'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('storm.cta_button.type.settings')
      ->set('allowed_values', $form_state->getValue('allowed_values'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
