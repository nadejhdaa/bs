<?php

declare(strict_types=1);

namespace Drupal\kc_personal_account\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a KC Personal account form.
 */
final class KcUserEditForm extends FormBase {

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
  public function getFormId(): string {
    return 'kc_personal_account_kc_user_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $user = $this->currentUser()->getAccount();
    $profile_type = 'customer';

    $profile_storage = $this->entityTypeManager->getStorage('profile');
    $profile = $profile_storage->loadByUser($user, $profile_type);

    $form['email'] = [
      '#type' => 'email',
      '#default_value' => $user->getEmail(),
      '#title' => $this->t('E-mail'),
    ];

    $form['phone'] = [
      '#type' => 'textfield',
      '#default_value' => !empty($profile) ? $profile->field_phone->value : '',
      '#title' => $this->t('User phone'),
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User name'),
      '#default_value' => !empty($profile) ? $profile->field_name->value : '',
    ];

    $form['patronymic'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User patronymic'),
      '#default_value' => !empty($profile) ? $profile->field_patronymic->value : '',
    ];

    $form['surname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User surname'),
      '#default_value' => !empty($profile) ? $profile->field_surname->value : '',
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save changes'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }

}
