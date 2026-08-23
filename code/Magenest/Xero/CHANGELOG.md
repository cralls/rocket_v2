# Change Log
All notable changes to this extension will be documented in this file.
This extension adheres to [Magenest](http://magenest.com/).

Xero compatible with 
```
Magento Commerce 2.1.x, 2.2.x, 2.3.x, 2.4.x
Magento OpenSource 2.1.x, 2.2.x, 2.3.x, 2.4.x
```
## [3.4.2] - 2020-10-15
### Update
-   Optimize syncing progress
-   Remove duplicate records in syncing progress
-   Remove non alphabetic character from sync data.
-   Fix minor bugs

## [3.4.1] - 2020-07-14
### Update
-   Update Xero APIs connection details
-   Remove shipping fee in virtual order
-   Update callback url by store code 

## [3.4.0] - 2020-01-08
-   Xero now support new OAuth 2.0 protocol
### Update
-   Remove OAuth 1.0 deprecated protocol 
-   Add credit memo adjustment amount
-   Optimize syncing performance 
### Fix
-   Handle amount missing/exceeding between Magento and Xero
-   Fix invalid character synced to Xero

## [3.3.0] - 2019-01-21
-   Xero now compatible with Magento 2.3
### Update
-   Add tax exclusive/inclusive options in configuration
-   Change order/invoice handling logic with tax exclusive/inclusive
-   Add cron schedule log
### Fix
-   Fix tax in credit memo. add link to trouble shoot
-   Missing event in guest checkout
-   Key file upload
-   Xero inventory is empty
-   Invalid date range in Xero daily report

## [3.2.1] - 2018-10-01
### Update
1. Hotfixes

## [3.2.0] - 2018-08-08
### Update
1. Add Multiple Websites Synchronization.
2. Add Xml Logging.
3. Add Synchronization MassAction.
4. Improve performance.
5. Fix some bugs.

## [3.1.1] - 2018-05-16
### Update
1. Hotfixes.

## [3.1.0] - 2018-05-12
### Update
1. Improve performance.
2. Add Payment Mapping.
3. Add Taxes Mapping.
4. Add "Add To Queue" button in order, invoice, product, contact, creditmemo.
5. Fix some bugs.

## [3.0.0] - 2017-10-23
### Add
1. Compatible with 2.2

## [2.1.2] - 2017-07-21
### Update
1. Now support Public Apps.
2. Private App Config is now easier with text area to enter public/private key pair.
3. Account Mapping is now a selector instead of input text.
4. Improve performance.
5. Improve Interface.
6. Fix some bugs.


## [2.1.1] - 2017-05-08
### Patch
1. Fix some bugs.

## [2.1.0] - 2016-11-16
### Add new features
1. Allow synchronizing and updating Credit Memos from Magento 2 store into Xero.
2. Allow see sync information and sync a single Customer.
3. Allow see sync information and sync a single Product.
4. Allow see sync information and sync a single Order.
5. Allow see sync information and sync a single Invoice.
6. Allow checking report of daily requests.
7. Allow setting Accounts in Xero.
8. Allow add all Customers, Products, Orders, Invoices, Credit Memos to Queue.


## [1.0.0] - 2016-06-23
### Release 
1. Allow synchronizing and updating Customers from Magento 2 store into Xero.
2. Allow synchronizing and updating Products from Magento 2 store into Xero.
3. Allow synchronizing and updating Orders from Magento 2 store into Xero.
4. Allow synchronizing and updating Invoices from Magento 2 store into Xero.
