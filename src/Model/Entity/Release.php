<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Release Entity
 *
 * @property int $id
 * @property string $acronym
 * @property \Cake\I18n\FrozenDate $release_date
 * @property bool $enable_ranked_list
 * @property bool $enable_10_node_list
 * @property bool $enable_full_list
 * @property bool $enable_historical_list
 *
 * @property \App\Model\Entity\Listing[] $listings
 * @property \App\Model\Entity\Submission[] $submissions
 */
class Release extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'acronym' => true,
        'release_date' => true,
        'listings' => true,
        'submissions' => true,
    ];
}
