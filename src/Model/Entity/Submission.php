<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Submission Entity
 *
 * @property int $id
 * @property int|null $release_id
 * @property string|null $information_system
 * @property string|null $information_institution
 * @property string|null $information_storage_vendor
 * @property string|null $information_filesystem_type
 * @property int|null $information_client_nodes
 * @property int|null $information_client_total_procs
 * @property string|null $io500_score
 * @property string|null $io500_bw
 * @property string|null $io500_md
 * @property string|null $io500_tot_iops
 * @property string|null $information_data
 * @property bool $information_10_node_challenge
 * @property string|null $information_list_name
 * @property string|null $information_identifier
 * @property string|null $information_submitter
 * @property \Cake\I18n\FrozenDate|null $information_submission_date
 * @property string|null $information_embargo_end_date
 * @property string|null $information_storage_install_date
 * @property string|null $information_storage_refresh_date
 * @property string|null $information_filesystem_name
 * @property string|null $information_filesystem_version
 * @property string|null $information_client_procs_per_node
 * @property string|null $information_client_operating_system
 * @property string|null $information_client_operating_system_version
 * @property string|null $information_client_kernel_version
 * @property int|null $information_md_nodes
 * @property int|null $information_md_storage_devices
 * @property string|null $information_md_storage_type
 * @property string|null $information_md_volatile_memory_capacity
 * @property string|null $information_md_storage_interface
 * @property string|null $information_md_network
 * @property string|null $information_md_software_version
 * @property string|null $information_md_operating_system_version
 * @property int|null $information_ds_nodes
 * @property int|null $information_ds_storage_devices
 * @property string|null $information_ds_storage_type
 * @property string|null $information_ds_volatile_memory_capacity
 * @property string|null $information_ds_storage_interface
 * @property string|null $information_ds_network
 * @property string|null $information_ds_software_version
 * @property string|null $information_ds_operating_system_version
 * @property string|null $information_note
 * @property string|null $information_best
 * @property string|null $ior_easy_write
 * @property string|null $ior_easy_read
 * @property string|null $ior_hard_write
 * @property string|null $ior_hard_read
 * @property string|null $mdtest_easy_write
 * @property string|null $mdtest_easy_stat
 * @property string|null $mdtest_easy_delete
 * @property string|null $mdtest_hard_write
 * @property string|null $mdtest_hard_read
 * @property string|null $mdtest_hard_stat
 * @property string|null $mdtest_hard_delete
 * @property string|null $find_easy
 * @property string|null $find_hard
 * @property string $find_mixed
 * @property string|null $marker_score
 * @property string|null $marker_md
 * @property string|null $storage_data
 * @property string $status
 * @property bool|null $include_in_io500
 * @property \Cake\I18n\FrozenDate|null $valid_from
 * @property \Cake\I18n\FrozenDate|null $valid_to
 * @property string|null $cdcl_url
 *
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
 * @property \App\Model\Entity\Listing[] $listings
 */
class Submission extends Entity
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
        '*' => true
    ];
}
