# This extension is created as a test for Compucorp application process and is not ready for production deployment

# com.membershipperiod.civicrm

com.membershipperiod.civicrm is a CiviCRM extension that enables you keep record of the each membership creation or renewal.

For example:

If a membership commenced on 1 Jan 2014 and each term was of 12 months in length, by 1 Jan 2016 the member would be renewing for their 3rd term. The terms would be:

Term/Period 1: 1 Jan 2014 - 31 Dec 2014
Term/Period 2: 1 Jan 2015 - 31 Dec 2015
Term/Period 3: 1 Jan 2016 - 31 Dec 2016

## Requirements

* PHP v5.4+
* CiviCRM (4.7.x)

## Installation (Web UI)

This extension follows the standard installation method - if you've got a supported CiviCRM version and you've set up your extensions directory, it'll appear in the Manage Extensions list as 'Membership Period (com.membershipperiod.civicrm)'. Hit Install and viola!.

If you need help with installing extensions, try: https://wiki.civicrm.org/confluence/display/CRMDOC/Extensions

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl com.membershipperiod.civicrm@https://github.com/roliod/com.membershipperiod.civicrm/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
git clone https://github.com/roliod/com.membershipperiod.civicrm.git
```

## Usage

This extension goes to work when the following actions are taken:

* Create a new membership
* Renew an existing membership
* Add a contribution record (This is linked to the membership period)

To see the membership period in the front end do the following:

* Go to the Membership Dashboard
* Click on any contact
* Click on the Membership Periods Tab (Its the last one)


## Please Note

Contribution records are linked to membership periods only when a payment was taken for the existing membership or renewal.

Here is a [demo](http://ec2-18-216-147-214.us-east-2.compute.amazonaws.com/?q=civicrm/dashboard).
