<?php

require_once __DIR__ . '/BaseUnitTestCase.php';

/**
 * This is a generic test class for the extension (implemented with PHPUnit).
 */
class CRM_mprd_MembershipPeriodUnitTest extends BaseUnitTestCase {

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp()
  {
      parent::setUp();
  }

  public function tearDown()
  {
      parent::tearDown();
  }

  /**
   * Create a new membership where payment is taken
   */
  public function testMembershipPeriodNew()
  {
      $contact_params = array(
        'sequential' => 1,
        'first_name' => "Can",
        'last_name' => "ada",
        'contact_type' => "Individual",
      );

      //create contact
      $contact = $this->create_contact($contact_params);

      if ($contact['status'] == false) {

          self::assertTrue(FALSE);

      } else {
          $membership_params = array(
             'contact_id' => $contact['message'],
             'membership_type_id' => 2,
             'join_date' => '20180406',
             'start_date' => '20180406',
             'end_date' => '20180430',
             'source' => 'Payment001',
             'status_id' => 1,
             'is_override' => null,
             'max_related' => null,
             'is_pay_later' => 0
          );

          //create membership
          $membership = $this->create_membership($membership_params);

          if ($membership['status'] == false) {
              self::assertTrue(FALSE);
          } else {
              self::assertTrue(TRUE);
          }
      }
  }

  /**
   * Create a new membership where payment is not taken
   */
  public function testMembershipPeriodNewPaymentNotTaken()
  {
      $contact_params = array(
        'sequential' => 1,
        'first_name' => "Foo",
        'last_name' => "Bar",
        'contact_type' => "Individual",
      );

      //create contact
      $contact = $this->create_contact($contact_params);
      
      if ($contact['status'] == false) {

          self::assertTrue(FALSE);

      } else {
          $membership_params = array(
             'contact_id' => $contact['message'],
             'membership_type_id' => 2,
             'join_date' => '20180406',
             'start_date' => '20180406',
             'end_date' => '20181030',
             'source' => 'Payment002',
             'status_id' => 1,
             'is_override' => null,
             'max_related' => null,
             'is_pay_later' => 1
          );

          //create membership
          $membership = $this->create_membership($membership_params);

          if ($membership['status'] == false) {
              self::assertTrue(FALSE);
          } else {
              self::assertTrue(TRUE);
          }
      }
  }

  /**
   * Renew an existing membership
   */
  public function testMembershipPeriodRenewal()
  {
      $contact_params = array(
        'sequential' => 1,
        'first_name' => "Test",
        'last_name' => "Contact",
        'contact_type' => "Individual",
      );

      //create contact
      $contact = $this->create_contact($contact_params);

      if ($contact['status'] == false) {

          self::assertTrue(FALSE);

      } else {
          $membership_params = array(
             'contact_id' => $contact['message'],
             'membership_type_id' => 2,
             'join_date' => '20180406',
             'start_date' => '20180406',
             'end_date' => '20181030',
             'source' => 'Payment003',
             'status_id' => 1,
             'is_override' => null,
             'max_related' => null,
          );

          //create membership
          $membership = $this->create_membership($membership_params);

          if ($membership['status'] == false) {
              self::assertTrue(FALSE);
          } else {

              $renew_membership_params = array(
                 'id' => $membership['message'],
                 'membership_type_id' => 2,
                 'join_date' => '20180406',
                 'start_date' => '20181030',
                 'end_date' => '20191030',
              );

              //renew membership
              $renew_membership = $this->renew_membership($renew_membership_params, $membership['message']);

              if ($renew_membership['status'] == false) {
                  self::assertTrue(FALSE);
              } else {
                  self::assertTrue(TRUE);
              }
          }
      }
  }

  /**
   * Add a contibution where payment was taken
   */
  public function testContributionWithPayment()
  {
      $contact_params = array(
        'sequential' => 1,
        'first_name' => "Roland",
        'last_name' => "Oduberu",
        'contact_type' => "Individual",
      );

      //create contact
      $contact = $this->create_contact($contact_params);

      if ($contact['status'] == false) {

          self::assertTrue(FALSE);

      } else {
          $membership_params = array(
             'contact_id' => $contact['message'],
             'membership_type_id' => 2,
             'join_date' => '20180406',
             'start_date' => '20180406',
             'end_date' => '20180430',
             'source' => 'Payment004',
             'status_id' => 1,
             'is_override' => null,
             'max_related' => null,
             'is_pay_later' => 0
          );

          //create membership
          $membership = $this->create_membership($membership_params);

          if ($membership['status'] == false) {
              self::assertTrue(FALSE);
          } else {

              $contribution_params = array(
                  'contact_id' => $contact['message'],
                  'financial_type_id' => 3,
                  'payment_instrument_id' => 4,
                  'receive_date' => '20180408150700',
                  'non_deductible_amount' => 300,
                  'total_amount' => 300,
                  'fee_amount' => 0,
                  'net_amount' => 300,
                  'trxn_id' => null,
                  'currency' => 'USD',
                  'cancel_date' => null,
                  'cancel_reason' => null,
                  'receipt_date' => null,
                  'thankyou_date' => null,
                  'source' => null,
                  'is_pay_later' => 0,
                  'contribution_status_id' => 1,
                  'check_number' => null,
              );

              //make contribution
              $contribution = $this->make_contribution($contribution_params);

              if ($membership['status'] == false) {
                  self::assertTrue(FALSE);
              } else {
                  self::assertTrue(TRUE);
              }
          }

      }
  }

  /**
   * Add a contibution where payment was not taken
   */
  public function testContributionWithoutPayment()
  {
      $contact_params = array(
        'sequential' => 1,
        'first_name' => "Jasmine",
        'last_name' => "Greg",
        'contact_type' => "Individual",
      );

      //create contact
      $contact = $this->create_contact($contact_params);

      if ($contact['status'] == false) {

          self::assertTrue(FALSE);

      } else {
          $membership_params = array(
             'contact_id' => $contact['message'],
             'membership_type_id' => 2,
             'join_date' => '20180406',
             'start_date' => '20180406',
             'end_date' => '20180430',
             'source' => 'Payment005',
             'status_id' => 1,
             'is_override' => null,
             'max_related' => null,
             'is_pay_later' => 1
          );

          //create membership
          $membership = $this->create_membership($membership_params);

          if ($membership['status'] == false) {
              self::assertTrue(FALSE);
          } else {

              $contribution_params = array(
                  'contact_id' => $contact['message'],
                  'financial_type_id' => 3,
                  'payment_instrument_id' => 4,
                  'receive_date' => '20180408150700',
                  'non_deductible_amount' => 400,
                  'total_amount' => 400,
                  'fee_amount' => 0,
                  'net_amount' => 300,
                  'trxn_id' => null,
                  'currency' => 'USD',
                  'cancel_date' => null,
                  'cancel_reason' => null,
                  'receipt_date' => null,
                  'thankyou_date' => null,
                  'source' => null,
                  'is_pay_later' => 0,
                  'contribution_status_id' => 1,
                  'check_number' => null,
              );

              //make contribution
              $contribution = $this->make_contribution($contribution_params);

              if ($membership['status'] == false) {
                  self::assertTrue(FALSE);
              } else {
                  self::assertTrue(TRUE);
              }
          }

      }
  }

}
