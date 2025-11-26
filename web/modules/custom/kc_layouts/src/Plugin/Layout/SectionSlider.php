<?php

namespace Drupal\kc_layouts\Plugin\Layout;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
* A very advanced custom layout.
*
* @Layout(
*   id = "section_slider",
*   label = @Translation("Section slider"),
*   category = @Translation("Sections"),
*   path = "layouts/section_slider",
*   template = "section-slider",
* )
*/

class SectionSlider extends LayoutDefault {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->pluginDefinition->setRegions($this->buildRegions());
  }


  /**
   * Builds regions from tab configuration.
   *
   * @param array $section
   *   Tabs.
   *
   * @return array
   *   Regions.
   */
  protected function buildRegions() : array {
    $regions = [];

    $regions['main'] = [
      'label' => new TranslatableMarkup('Main', [], ['context' => 'layout_region']),
    ];

    return $regions;
  }

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['container'] = [
      '#type' => 'select',
      '#title' => $this->t('Container class'),
      '#options' => $this->getContainerOptions(),
      '#default_value' => !empty($this->configuration['container']) ? $this->configuration['container'] : [],
    ];

    $form['section_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Add class'),
      '#default_value' => !empty($this->configuration['section_class']) ? $this->configuration['section_class'] : [],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritDoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['section_class'] = $form_state->getValue('section_class');
    $this->configuration['container'] = $form_state->getValue('container');
  }


  public function getContainerOptions() {
    return [
      'container' => 'container',
      'container-fluid' => 'container-fluid',
    ];
  }

}
