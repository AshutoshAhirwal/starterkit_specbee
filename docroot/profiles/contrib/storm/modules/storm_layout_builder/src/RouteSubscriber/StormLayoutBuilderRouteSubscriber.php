<?php

namespace Drupal\storm_layout_builder\RouteSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Storm layout builder event subscriber.
 */
class StormLayoutBuilderRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('layout_builder.choose_block')) {
      $route->setDefault('_title', 'Add a Component');
      $route->setDefault('_controller', '\Drupal\storm_layout_builder\Controller\StormBrowseComponentController::browse');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -150];
    return $events;
  }

}
