<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Listing Entity
 *
 * @property int $id
 * @property int $type_id
 * @property int $release_id
 * @property string $description
 *
 * @property \App\Model\Entity\Type $type
 * @property \App\Model\Entity\Release $release
 * @property \App\Model\Entity\ListIsc18Io500[] $list_isc18_io500
 * @property \App\Model\Entity\ListIsc1910node[] $list_isc1910node
 * @property \App\Model\Entity\ListIsc19Full[] $list_isc19_full
 * @property \App\Model\Entity\ListIsc19Io500[] $list_isc19_io500
 * @property \App\Model\Entity\ListIsc2010node[] $list_isc2010node
 * @property \App\Model\Entity\ListIsc20Full[] $list_isc20_full
 * @property \App\Model\Entity\ListIsc20Historical[] $list_isc20_historical
 * @property \App\Model\Entity\ListIsc20Io500[] $list_isc20_io500
 * @property \App\Model\Entity\ListIsc2110node[] $list_isc2110node
 * @property \App\Model\Entity\ListIsc21Full[] $list_isc21_full
 * @property \App\Model\Entity\ListIsc21Historical[] $list_isc21_historical
 * @property \App\Model\Entity\ListIsc21Io500[] $list_isc21_io500
 * @property \App\Model\Entity\ListSc17Io500[] $list_sc17_io500
 * @property \App\Model\Entity\ListSc1810node[] $list_sc1810node
 * @property \App\Model\Entity\ListSc18Io500[] $list_sc18_io500
 * @property \App\Model\Entity\ListSc18Star10node[] $list_sc18_star10node
 * @property \App\Model\Entity\ListSc18StarIo500[] $list_sc18_star_io500
 * @property \App\Model\Entity\ListSc1910node[] $list_sc1910node
 * @property \App\Model\Entity\ListSc19Full[] $list_sc19_full
 * @property \App\Model\Entity\ListSc19Historical[] $list_sc19_historical
 * @property \App\Model\Entity\ListSc19Io500[] $list_sc19_io500
 * @property \App\Model\Entity\ListSc19Scc[] $list_sc19_scc
 * @property \App\Model\Entity\ListSc2010node[] $list_sc2010node
 * @property \App\Model\Entity\ListSc20Full[] $list_sc20_full
 * @property \App\Model\Entity\ListSc20Historical[] $list_sc20_historical
 * @property \App\Model\Entity\ListSc20Io500[] $list_sc20_io500
 * @property \App\Model\Entity\Submission[] $submissions
 */
class Listing extends Entity
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
        'type_id' => true,
        'release_id' => true,
        'description' => true,
        'type' => true,
        'release' => true,
        'submissions' => true,
    ];
}
