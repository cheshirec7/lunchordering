import {
  Component,
  OnInit
} from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { Auth, Broadcaster } from '../_services';
import { User } from './user.interface';
import { EVENTS } from '../events';

@Component({
  selector: 'login',
  styleUrls: ['./login.component.css'],
  templateUrl: './login.component.html'
})
export class LoginComponent implements OnInit {
  public user: User = {
    email: '',
    password: ''
  };
  public loading = false;
  public returnUrl: string;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private auth: Auth,
    private broadcaster: Broadcaster) {
  }

  public ngOnInit() {
    this.auth.logout();
    this.broadcaster.broadcast(EVENTS.SPINNER.SPINNING, false);
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/orders';
  }

  public login(model: User, isValid: boolean) {

    if (!isValid) {
      let err = {
        header: 'Alert',
        msg: 'Invalid email or password'
      };
      this.broadcaster.broadcast(EVENTS.AUTH.LOGIN_FAILURE, err);
      return false;
    }

    this.broadcaster.broadcast(EVENTS.SPINNER.SPINNING, true);
    // this.model.email, this.model.password
    this.auth.login(model)
      .subscribe(
      (response) => {
        // console.log('login');
        // console.log(response);
        if (response.hasOwnProperty('error')) {
          this.broadcaster.broadcast(EVENTS.AUTH.LOGIN_FAILURE, response);
          this.broadcaster.broadcast(EVENTS.SPINNER.SPINNING, false);
        } else {
          this.router.navigate([this.returnUrl]);
        }
      });
  }
}
