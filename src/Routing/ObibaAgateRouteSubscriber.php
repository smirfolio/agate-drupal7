<?php
namespace Drupal\obiba_agate\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */


class ObibaAgateRouteSubscriber extends RouteSubscriberBase {

    /**
     * {@inheritdoc}
     */
    public function alterRoutes(RouteCollection $collection) {
        // Lookup the route object by its route_name
        if ($route = $collection->get('user.pass')) {
            // Override the view controller with an extended version of it
            $route->setDefault(
                '_form',
                'Drupal\obiba_agate\Form\AgatePasswordReset'
            );
        }
    }

}