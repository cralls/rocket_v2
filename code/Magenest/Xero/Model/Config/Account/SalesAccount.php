<?php
namespace Magenest\Xero\Model\Config\Account;

class SalesAccount extends XeroAccount
{
    protected $types = ['SALES', 'REVENUE'];

    protected $useCode = true;
}
