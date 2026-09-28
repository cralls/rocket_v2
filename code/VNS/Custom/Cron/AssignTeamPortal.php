<?php
namespace VNS\Custom\Cron;

use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/** Assign an order's portal from the products that were actually purchased. */
class AssignTeamPortal
{
    private const TEAM_PORTALS_CATEGORY_ID = 108;
    private const LOOKBACK_DAYS = 90;

    private $resourceConnection;
    private $logger;

    public function __construct(ResourceConnection $resourceConnection, LoggerInterface $logger)
    {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    public function execute()
    {
        $connection = $this->resourceConnection->getConnection();
        $orderTable = $this->resourceConnection->getTableName('sales_order');
        $itemTable = $this->resourceConnection->getTableName('sales_order_item');
        $productCategoryTable = $this->resourceConnection->getTableName('catalog_category_product');
        $categoryTable = $this->resourceConnection->getTableName('catalog_category_entity');
        $since = gmdate('Y-m-d H:i:s', time() - self::LOOKBACK_DAYS * 86400);

        // Sales order items include the configurable parent as well as its selected simple.
        // Count distinct portal categories so an order is never assigned arbitrarily.
        $select = $connection->select()
            ->from(['o' => $orderTable], ['entity_id'])
            ->join(['i' => $itemTable], 'i.order_id = o.entity_id', [])
            ->join(['cp' => $productCategoryTable], 'cp.product_id = i.product_id', [])
            ->join(
                ['c' => $categoryTable],
                'c.entity_id = cp.category_id AND c.parent_id = ' . self::TEAM_PORTALS_CATEGORY_ID,
                []
            )
            ->columns([
                'portal_count' => new \Zend_Db_Expr('COUNT(DISTINCT c.entity_id)'),
                'portal_id' => new \Zend_Db_Expr('MIN(c.entity_id)')
            ])
            ->where('o.created_at >= ?', $since)
            ->where('(o.team_portal IS NULL OR o.team_portal = 0)')
            ->group('o.entity_id');

        $assigned = 0;
        $ambiguous = [];
        foreach ($connection->fetchAll($select) as $candidate) {
            if ((int)$candidate['portal_count'] !== 1) {
                $ambiguous[] = (int)$candidate['entity_id'];
                continue;
            }

            // A concurrent checkout or another cron run may have assigned this order.
            $assigned += $connection->update(
                $orderTable,
                ['team_portal' => (int)$candidate['portal_id']],
                $connection->quoteInto(
                    'entity_id = ? AND (team_portal IS NULL OR team_portal = 0)',
                    (int)$candidate['entity_id']
                )
            );
        }

        if ($assigned) {
            $this->logger->info('Assigned team portals to ' . $assigned . ' orders.');
        }
        if ($ambiguous) {
            $this->logger->warning(
                'Team portal assignment skipped orders with products in multiple portals: '
                . implode(', ', $ambiguous)
            );
        }
    }
}
