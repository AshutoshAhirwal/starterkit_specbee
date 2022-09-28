<?php

namespace Specbee\Storm;

use Symfony\Component\Yaml\Yaml;

/**
 * Overwrite configuration already exist in active configuration.
 */
class StormConfigOverwrite {

  /**
   * Alter the active configuration.
   *
   * @param string $config_path
   *   The config path.
   * @param string $config
   *   The config.
   */
  public static function alter($config_path, $config) {
    $config_yaml = Yaml::parse(file_get_contents($config_path));

    // Add new config using config factory.
    \Drupal::configFactory()
      ->getEditable($config)
      ->setData($config_yaml)
      ->save();
  }

}
