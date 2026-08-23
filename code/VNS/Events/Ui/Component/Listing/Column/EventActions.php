<?php
namespace VNS\Events\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class EventActions extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$fieldName]['edit'] = [
                    'href' => $this->getContext()->getUrl(
                        'events/event/edit',
                        ['event_id' => $item['event_id']]
                    ),
                    'label' => __('Edit'),
                ];
                $item[$fieldName]['delete'] = [
                    'href' => $this->getContext()->getUrl(
                        'events/event/delete',
                        ['event_id' => $item['event_id']]
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete Event'),
                        'message' => __('Are you sure you want to delete this event?'),
                    ],
                ];
            }
        }
        return $dataSource;
    }
}
