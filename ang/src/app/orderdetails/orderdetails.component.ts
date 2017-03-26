import { Component, OnInit, AfterViewInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Api, NavHelper, Auth, Broadcaster } from '../_services';
import { EVENTS } from '../events';

@Component({
  selector: 'orderdetails',
  templateUrl: './orderdetails.component.html',
  styleUrls: ['./orderdetails.component.css'],
})
export class OrderDetailsComponent implements OnInit, AfterViewInit {

  public menu: any;
  public totalPrice = '$0.00';

  constructor(
    private api: Api,
    private route: ActivatedRoute,
    private nav: NavHelper,
    private router: Router,
    private auth: Auth,
    private broadcaster: Broadcaster) {
  }

  public ngOnInit() {
    if (this.route.snapshot.data['menu'].hasOwnProperty('error')) {
      this.broadcaster.broadcast(EVENTS.ERROR.DBERROR, this.route.snapshot.data['menu']);
      this.nav.redirectToLogin();
    } else {
      this.menu = this.route.snapshot.data['menu'];
      this.updateTotalPrice(0, false, false);
    }
  }

  public ngAfterViewInit() {
    this.broadcaster.broadcast(EVENTS.SPINNER.SPINNING, false);
    window.scrollTo(0, 0);
  }

  public updateTotalPrice(id, flipCheckedState, validateQty) {
    let newprice = 0;
    this.menu.menuitems
      .map((menuitem) => {
        if (menuitem.id === id) {
          menuitem.checked = (flipCheckedState) ? !menuitem.checked : menuitem.checked;
          menuitem.qty = (menuitem.checked) ? 1 : 0;
        }

        if (validateQty && menuitem.qty < 1) {
          menuitem.qty = 0;
          menuitem.checked = false;
        }

        newprice += (menuitem.qty * menuitem.price);
      });
    this.totalPrice = '$' + (newprice / 100).toFixed(2);
  }

  public onSubmit() {

    this.broadcaster.broadcast(EVENTS.SPINNER.SPINNING, true);

    let data = {
      ts: this.route.snapshot.params['ts'],
      acctid: this.auth.accID,
      uid: this.route.snapshot.params['uid'],

      menuids: this.menu.menuitems
        .filter((menuitem) => menuitem.checked)
        .map((menuitem) => menuitem.id),

      qtys: this.menu.menuitems
        .filter((menuitem) => menuitem.checked)
        .map((menuitem) => menuitem.qty)
    };

    this.api.post('orders', JSON.stringify(data)).subscribe(
      (response) => {
        if (response.hasOwnProperty('error')) {
          this.broadcaster.broadcast(EVENTS.ERROR.DBERROR, response);
          this.nav.redirectToLogin();
        } else {
          this.router.navigate(['/orders']);
        }
      }
    );
  };
}
