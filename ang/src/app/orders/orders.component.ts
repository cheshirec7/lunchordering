import {
  Component, OnInit, OnDestroy, trigger, state, style,
  transition, animate, ViewChild, AfterViewInit
} from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { MdTabGroup } from '@angular/material';
import { Api, NavHelper, Broadcaster, Auth } from '../_services';
import { EVENTS } from '../events';
import { NotificationsService } from 'angular2-notifications';

@Component({
  selector: 'orders',
  templateUrl: './orders.component.html',
  styleUrls: ['./orders.component.css'],
  animations: [
    trigger('visibilityChanged', [
      state('shown', style({ opacity: 1 })),
      state('hidden', style({ opacity: 0 })),
      transition('* => *', animate('500ms'))
    ])
  ]
})
export class OrdersComponent implements OnInit, OnDestroy, AfterViewInit {

  public orders: any;
  public dateRange = '';
  public isPast = false;
  public visibilityChanged = 'shown';
  @ViewChild(MdTabGroup) public tabGroup: MdTabGroup;
  private obsTabChange: any;

  constructor(
    private api: Api,
    private route: ActivatedRoute,
    private nav: NavHelper,
    private auth: Auth,
    private broadcaster: Broadcaster,
    private _notificationsService: NotificationsService) {
  }

  public ngOnInit() {
    // console.log('orders');
    // console.log(this.route.snapshot.data['orders']);
    if (this.route.snapshot.data['orders'].hasOwnProperty('error')) {
      this.broadcaster.broadcast(EVENTS.ERROR.DBERROR, this.route.snapshot.data['orders']);
      this.nav.redirectToLogin();
    } else {
      this.orders = this.route.snapshot.data['orders'];
      this.setDateRange();
      this.obsTabChange = this.tabGroup.selectedIndexChange.subscribe((num) => {
        this.nav.selectedTab = num;
      });
    }
  };

  public ngAfterViewInit() {
    this.tabGroup.selectedIndex = this.nav.selectedTab;
    this.broadcaster.broadcast(EVENTS.SPINNER.SPINNING, false);
  }

  public prev() {
    this.getData(-1);
  }

  public next() {
    this.getData(1);
  }

  public ngOnDestroy() {
    if (this.obsTabChange) {
      this.obsTabChange.unsubscribe();
    }
  }

  private setDateRange() {
    this.dateRange = this.orders[0].details[0].shortdate + ' - ' +
      this.orders[0].details[4].shortdate;
  }

  private getData(periodOffset) {
    this.broadcaster.broadcast(EVENTS.SPINNER.SPINNING, true);
    this.nav.curPeriodNo += periodOffset;
    this.isPast = this.nav.curPeriodNo < 0;
    this.visibilityChanged = 'hidden';

    // TODO: need to unsubscribe?
    this.api.get(this.nav.getOrdersURL()).subscribe(
      (response) => {
        // console.log(response);
        if (response.hasOwnProperty('error')) {
          this.orders = null;
          this.broadcaster.broadcast(EVENTS.ERROR.DBERROR, response);
          this.nav.redirectToLogin();
        } else {
          this.orders = response;
          this.setDateRange();
          this.visibilityChanged = 'shown';
          this.tabGroup.selectedIndex = this.nav.selectedTab;
          this.broadcaster.broadcast(EVENTS.SPINNER.SPINNING, false);
        }
      }
    );
  }
}
