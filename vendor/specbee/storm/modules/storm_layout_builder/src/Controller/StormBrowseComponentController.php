<?php

namespace Drupal\storm_layout_builder\Controller;

use Drupal\layout_builder_browser\Controller\BrowserController;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Returns responses for Storm layout builder routes.
 */
class StormBrowseComponentController extends BrowserController {

  /**
   * {@inheritdoc}
   */
  public function browse(SectionStorageInterface $section_storage, $delta, $region) {
    $build = parent::browse($section_storage, $delta, $region);
    $build['filter']['#access'] = FALSE;
    $build['block_categories']['component']['#type'] = 'container';
    $build['block_categories']['component']['#attributes']['class'][0] = 'storm-component-browser';
    $build['#attached']['library'][] = 'storm_layout_builder/browser';

    return $build;
  }

}
