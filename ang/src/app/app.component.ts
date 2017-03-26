import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { Auth, Broadcaster } from './_services';
import { EVENTS } from './events';
import { NotificationsService } from 'angular2-notifications';

@Component({
  selector: 'app',
  encapsulation: ViewEncapsulation.None,
  styleUrls: [
    './app.component.css'
  ],
  templateUrl: './app.component.html'
})
export class AppComponent implements OnInit {
  public ccaLogo = 'assets/img/ccalogo.png';
  public name = 'Chandler Christian Academy';
  public url = 'http://chandlerchristianacademy.org';
  public loading = false;

  public notifyOptions = {
    position: ['bottom', 'right'],
    timeOut: 5000,
    lastOnBottom: true
  };

  public menuItems = [
    { id: 'home', text: 'Home', icon: 'home', selected: 'selected', url: './' },
    { id: 'orders', text: 'My orders', icon: 'event', selected: '', url: './orders' },
    {
      id: 'myaccount', text: 'My account',
      icon: 'account_balance', selected: '', url: './myaccount'
    },
    { id: 'logout', text: 'Logout', icon: 'exit_to_app', selected: '', url: './logout' }
  ];

  constructor(
    private auth: Auth,
    private broadcaster: Broadcaster,
    private _notificationsService: NotificationsService) {
  }

  public ngOnInit() {

    this.broadcaster.subscribe(EVENTS.ERROR.DBERROR, (data) => {
      // console.log('dberror');
      // console.log(data);
      this._notificationsService.error(
        data.header,
        data.msg,
        {
          timeOut: 4000,
          showProgressBar: true,
          pauseOnHover: false,
          clickToClose: true,
          maxLength: 100
        }
      );
    });

    this.broadcaster.subscribe(EVENTS.AUTH.LOGIN_FAILURE, (data) => {
      // console.log('logfail');
      // console.log(data);
      this._notificationsService.error(
        data.header,
        data.msg,
        {
          timeOut: 4000,
          showProgressBar: true,
          pauseOnHover: false,
          clickToClose: true,
          maxLength: 100
        }
      );
    });

    this.broadcaster.subscribe(EVENTS.SPINNER.SPINNING, (data) => {
      // if (data)
      this.loading = data;
    });

    this.broadcaster.subscribe(EVENTS.MAINMENU.SETSELECTED, (data) => {

      this.menuItems.forEach((item) => {
        if ((item.id === data) || (item.id === 'orders' && data === 'orderdetails')) {
          item.selected = 'selected';
        } else {
          item.selected = '';
        }
      });
    });

    if (this.auth.getUser()) {
      this.auth.verify().subscribe();
      // (data) => {
      // },
      // (error) => {
      // });
    }
  }

}
