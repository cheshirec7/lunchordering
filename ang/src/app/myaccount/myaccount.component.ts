import {
  Component,
  OnInit,
  AfterViewInit
} from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { NavHelper, Broadcaster } from '../_services';
import { EVENTS } from '../events';

@Component({
  selector: 'myaccount',
  styleUrls: ['./myaccount.component.css'],
  templateUrl: './myaccount.component.html'
})
export class MyAccountComponent implements OnInit, AfterViewInit {

  public details: any;
  public totalOfOrders = 0.00;
  public totalOrders = 0;
  public curBal = '$0.00';
  public curBalClass = '';

  constructor(
    private route: ActivatedRoute,
    private nav: NavHelper,
    private broadcaster: Broadcaster) {
  }

  public ngOnInit() {
    this.route.data.subscribe((data: any) => {
      if (data.details.hasOwnProperty('error')) {
        this.broadcaster.broadcast(EVENTS.ERROR.DBERROR, this.route.snapshot.data.details);
        this.nav.redirectToLogin();
      } else {
        this.details = data.details;
        if (this.details.cur_balance < 0) {
          this.curBal = '($' + (-this.details.cur_balance / 100).toFixed(2) + ')';
          this.curBalClass = 'table-danger';
        } else if (this.details.cur_balance > 0) {
          this.curBal = '$' + (this.details.cur_balance / 100).toFixed(2);
          this.curBalClass = 'table-success';
        }

        for (let obj of this.details.order_aggs) {
          this.totalOfOrders += +obj.total_price;
          this.totalOrders += +obj.order_count;
        }
      }
    });
  }

  public ngAfterViewInit() {
    this.broadcaster.broadcast(EVENTS.SPINNER.SPINNING, false);
  }

}
