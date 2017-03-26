import {
  Component,
  OnInit
} from '@angular/core';
import { Auth, Broadcaster } from '../_services';
import { EVENTS } from '../events';

@Component({
  selector: 'logout',
  styleUrls: ['./logout.component.css'],
  templateUrl: './logout.component.html'
})
export class LogoutComponent implements OnInit {

  constructor(
    private auth: Auth,
    private broadcaster: Broadcaster) {
  }

  public ngOnInit() {
    this.auth.logout();
    this.broadcaster.broadcast(EVENTS.MAINMENU.SETSELECTED, 'logout');
  }
}
