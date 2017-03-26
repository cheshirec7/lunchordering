import { Resolve, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { Injectable } from '@angular/core';
import { Api, NavHelper, Auth, Broadcaster } from './_services';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/observable/of';
import 'rxjs/add/operator/catch';
import { EVENTS } from './events';

@Injectable()
export class DataResolver implements Resolve<any> {

  constructor(
    private api: Api,
    private nav: NavHelper,
    private auth: Auth,
    private broadcaster: Broadcaster) {
  }

  public resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {

    this.broadcaster.broadcast(EVENTS.MAINMENU.SETSELECTED, route.url[0].path);
    switch (route.url[0].path) {
      case 'orders':
        this.broadcaster.broadcast(EVENTS.SPINNER.SPINNING, true);
        return this.api.get(this.nav.getOrdersURL());
      case 'orderdetails':
        this.broadcaster.broadcast(EVENTS.SPINNER.SPINNING, true);
        return this.api.get('menu/' + route.params['ts'] + '/' + route.params['uid']);
      case 'myaccount':
        this.broadcaster.broadcast(EVENTS.SPINNER.SPINNING, true);
        return this.api.get('myaccount/' + this.auth.accID);
      default:
        return 'error';
    }
  }
}

export const APP_RESOLVER_PROVIDERS = [
  DataResolver
];
