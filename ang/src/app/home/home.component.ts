import {
  Component,
  OnInit
} from '@angular/core';
import { Broadcaster } from '../_services';
import { EVENTS } from '../events';

@Component({
  selector: 'home',
  styleUrls: ['./home.component.css'],
  templateUrl: './home.component.html'
})
export class HomeComponent implements OnInit {

  constructor(
    private broadcaster: Broadcaster) {
  }

  public ngOnInit() {
    this.broadcaster.broadcast(EVENTS.MAINMENU.SETSELECTED, 'home');
  };
}
