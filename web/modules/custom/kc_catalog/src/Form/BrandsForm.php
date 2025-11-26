<?php

namespace Drupal\kc_catalog\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

class BrandsForm extends FormBase {

  const VID = 'brands';

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an BrandsForm object.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'brands_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['search'] = [
      '#type' => 'textfield',
      '#field_prefix' => '<h4>' . $this->t('Brands') . '</h4>',
      '#wrapper_attributes' => [
        'class' => ['d-flex'],
      ],
      '#ajax' => [
        'callback' => '::filterBrandsAjaxCallback',
        'event' => 'keyup',
      ],
    ];

    $terms_data = $this->getTermsData();
    $form['links'] = [
      '#theme' => 'main_catalog_brand_links',
      '#terms_data' => $terms_data,
    ];

    return $form;
  }

  public function filterBrandsAjaxCallback(array $form, FormStateInterface $form_state) {
    $input = $form_state->getUserInput();
    $search = $input['search'];
    $strlen = strlen($input['search']);

    $response = new AjaxResponse();

    if ($strlen > 2 || $strlen == 0) {
      $terms_data = $this->getTermsData($input['search']);
      $form['links']['#terms_data'] = $terms_data;
      $response->addCommand(new ReplaceCommand('.main-catalog__body__brands-links', $form['links']));
    }

    return $response;
  }

  public function getTermsData($string = '') {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => self::VID,
    ]);

    if (!empty($string)) {
      $search = strtolower($string);
    }

    foreach ($terms as $term) {
      $term_name = $term->getName();
      $tid = $term->id();

      if (!empty($search)) {
        if (!str_contains(strtolower($term_name), $search)) {
          continue;
        }
      }


      $first_letter = substr($term_name, 0, 1);
      if (is_numeric($first_letter)) {
        $first_letter = '0..9';
      }
      else {
        $first_letter = strtoupper($first_letter);
      }

      $term_data['alphabetical'][$first_letter][$tid] = $term_name;

      if (!$term->get('field_brand_type')->isEmpty()) {
        $term_data['popular'][$tid] = $term_name;
      }
    }

    ksort($term_data['alphabetical']);

    return $term_data;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
