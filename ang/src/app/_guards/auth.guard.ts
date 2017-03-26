import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { Auth } from '../_services';

@Injectable()
export class AuthGuard implements CanActivate {

  constructor(
    private router: Router,
    private auth: Auth) {
  }

  public canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {

    switch (state.url) {
      case '/orders':
      case '/menu':
      case '/myaccount':
        if (!this.auth.hasUserPrivileges()) {
          this.router.navigate(['/login'], { queryParams: { returnUrl: '/orders' } });
          return false;
        }
      default:
        return true;
    }
  }
}
