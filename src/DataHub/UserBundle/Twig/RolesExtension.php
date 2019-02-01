<?php

namespace DataHub\UserBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RolesExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new TwigFilter('roleLabels', array($this, 'roleLabels')),
        );
    }

    /**
     * Twig filter. Filters roles and returns translateable label.
     */
    public function roleLabels($roles)
    {
        if (count($roles) > 1) {
            $roles = array_filter(
                $roles, function ($role) {
                    return ($role != "ROLE_CONSUMER");
                }
            );
        }

        array_walk(
            $roles, function (&$role, $key) {
                $labels = array(
                    'ROLE_CONSUMER' => 'roles.consumer',
                    'ROLE_MANAGER' => 'roles.manager',
                    'ROLE_ADMIN' => 'roles.administrator',
                );
                $role = $labels[$role];
            }
        );

        // Pop up the top role.
        return array_pop($roles);
    }
}
