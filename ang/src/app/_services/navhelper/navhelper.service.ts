import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Auth } from '../auth';

@Injectable()
export class NavHelper {

  public curPeriodNo = 0;
  public selectedTab = 0;

  constructor(private auth: Auth, private router: Router) {
  }

  public getOrdersURL() {
    return 'orders/' + this.auth.accID + '/' + this.curPeriodNo;
  }

  public redirectToLogin() {
    this.router.navigate(['/login'], { queryParams: { returnUrl: '/orders' } });
  }
}
