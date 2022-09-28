<?php

namespace Drupal\storm_layout_builder\Plugin\Layout;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to provides extra configurations for layouts.
 */
class StormLayout extends LayoutDefault implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The styles group plugin manager.
   *
   * @var \Drupal\bootstrap_styles\StylesGroup\StylesGroupManager
   */
  protected $stylesGroupManager;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'storm_layout_builder/form';

    $form['section_title'] = [
      '#type' => 'details',
      '#title' => $this->t('Section title'),
      '#weight' => 1,
    ];

    $form['section_title']['markup'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('Provide an optional title to the layout section') . '</p>',
    ];

    $form['section_title']['heading_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Style'),
      '#options' => [
        'h1' => $this->t('H1'),
        'h2' => $this->t('H2'),
        'h3' => $this->t('H3'),
        'h4' => $this->t('H4'),
        'h5' => $this->t('H5'),
        'h6' => $this->t('H6'),
      ],
      '#default_value' => $this->configuration['section_title']['heading_style'] ?? 'h1',
    ];

    $form['section_title']['heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->configuration['section_title']['heading'] ?? '',
    ];

    $form['section_title']['heading_alignment'] = [
      '#type' => 'select',
      '#title' => $this->t('Alignment'),
      '#options' => [
        'text-align-left' => $this->t('Left'),
        'text-align-right' => $this->t('Right'),
        'text-align-center' => $this->t('Center'),
      ],
      '#default_value' => $this->configuration['section_title']['heading_alignment'] ?? 'text-align-left',
    ];

    $form['section_background'] = [
      '#type' => 'details',
      '#title' => $this->t('Background'),
      '#weight' => 3,
    ];

    $form['section_background']['background_color'] = [
      '#type' => 'radios',
      '#options' => $this->getColors(),
      '#default_value' => $this->configuration['section_background']['background_color'] ?? 'slb-bg-none',
    ];

    $form['section_background']['background_media'] = [
      '#type' => 'media_library',
      '#allowed_bundles' => ['image'],
      '#title' => $this->t('Background Image'),
      '#default_value' => $this->configuration['section_background']['background_media'] ?? NULL,
      '#description' => $this->t('Upload or select a background image.'),
    ];
    $form['section_background']['background_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Background image position'),
      '#options' => [
        'left top' => $this->t('Left top'),
        'left center' => $this->t('Left center'),
        'left bottom' => $this->t('Left bottom'),
        'center top' => $this->t('Center top'),
        'center center' => $this->t('Center center'),
        'center bottom' => $this->t('Center bottom'),
        'right top' => $this->t('Right top'),
        'right center' => $this->t('Right center'),
        'right bottom' => $this->t('Right bottom'),
      ],
      '#default_value' => $this->configuration['section_background']['background_position'] ?? 'center center',
    ];

    $form['section_background']['background_size'] = [
      '#type' => 'select',
      '#title' => $this->t('Background image size'),
      '#options' => [
        'cover' => $this->t('Cover'),
        'contain' => $this->t('Contain'),
      ],
      '#default_value' => $this->configuration['section_background']['background_size'] ?? 'cover',
    ];

    $form['section_background']['background_repeat'] = [
      '#type' => 'select',
      '#title' => $this->t('Background image repeat'),
      '#options' => [
        'repeat' => $this->t('Repeat'),
        'no-repeat' => $this->t('No repeat'),
        'repeat-x' => $this->t('Repeat X'),
        'repeat-y' => $this->t('Repeat Y'),
      ],
      '#default_value' => $this->configuration['section_background']['background_repeat'] ?? 'no-repeat',
    ];

    $form['section_padding'] = [
      '#type' => 'details',
      '#title' => $this->t('Padding'),
      '#weight' => 3,
    ];

    $padding = [
      'padding_top' => $this->t('Top padding'),
      'padding_bottom' => $this->t('Bottom padding'),
      'padding_left' => $this->t('Left padding'),
      'padding_right' => $this->t('Right padding'),
    ];

    foreach ($padding as $key => $value) {
      $form['section_padding'][$key] = [
        '#type' => 'select',
        '#title' => $value,
        '#options' => [
          'none' => $this->t('None'),
        ],
        '#empty_value' => 'none',
        '#default_value' => $this->configuration['section_padding'][$key] ?? 'none',
      ];
      $form['section_padding'][$key]['#options'] = $this->getOptions($key);
    }

    $form['section_spacing'] = [
      '#type' => 'details',
      '#title' => $this->t('Spacing'),
      '#weight' => 3,
    ];

    $spacing = [
      'spacing_top' => $this->t('Top spacing'),
      'spacing_bottom' => $this->t('Bottom spacing'),
      'spacing_left' => $this->t('Left spacing'),
      'spacing_right' => $this->t('Right spacing'),
    ];

    foreach ($spacing as $key => $value) {
      $form['section_spacing'][$key] = [
        '#type' => 'select',
        '#title' => $value,
        '#options' => [
          'none' => $this->t('None'),
        ],
        '#empty_value' => 'none',
        '#default_value' => $this->configuration['section_spacing'][$key] ?? 'none',
      ];
      $form['section_spacing'][$key]['#options'] = $this->getOptions($key);
    }

    $form['section_theme'] = [
      '#type' => 'details',
      '#title' => $this->t('Theme'),
      '#weight' => 3,
    ];

    $form['section_theme']['styles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Style'),
      '#multiple' => TRUE,
      '#options' => $this->getOptions('styles'),
      '#default_value' => $this->configuration['section_theme']['styles'] ?? [],
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * Helper method to build color options scheme.
   */
  private function getColors() {
    $config = $this->configFactory->get('storm.layout_builder.settings')->get('background_colors');
    $colors = $this->getConfigValues($config);

    foreach ($colors as $class => $color) {
      $options[$class] = "<span class='" . $class . "' title='" . $color . "'></span>";
    }

    $options = ['slb-bg-none' => "<span class='slb-bg-none' title='None'></span>"] + $options;

    return $options;
  }

  /**
   * Helper method to get padding options.
   */
  private function getOptions($key) {
    $config = $this->configFactory->get('storm.layout_builder.settings')->get($key);
    return $this->getConfigValues($config);
  }

  /**
   * Build the config values array from config object.
   */
  private function getConfigValues($config) {
    foreach (explode("\r\n", $config) as $color) {
      $color = trim($color);
      if (!empty($color)) {
        [$class, $label] = explode('|', $color);
        $options[$class] = $label;
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    foreach ($form_state->getValues() as $key => $config) {
      $this->configuration[$key] = $config;
    }
  }

}
