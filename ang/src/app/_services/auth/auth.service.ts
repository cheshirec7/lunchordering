import { Injectable } from '@angular/core';
import { Api } from '../api';
import { Broadcaster } from '../broadcaster';
import { EVENTS } from '../../events';

@Injectable()
export class Auth {

  private currentUser = null;
  private token = null;
  private privilegeLevelSuper = 100;
  private privilegeLevelAdmin = 50;
  private privilegeLevelUser = 1;

  constructor(
    private api: Api,
    private broadcaster: Broadcaster) {

    let user = localStorage.getItem('currentUser');
    this.token = localStorage.getItem('token');
    if (user && this.token) {
      this.currentUser = JSON.parse(user);
      this.api.addDefaultHeader('Authorization', 'Bearer ' + this.token);
    } else {
      this.clearUser();
      this.clearToken();
    }
  }

  public getUser() {
    return this.currentUser;
  }

  public get accID() {
    return this.currentUser.id;
  }

  public hasUserPrivileges() {
    if (this.currentUser) {
      return (this.currentUser.privilege_level >= this.privilegeLevelUser);
    }

    return false;
  }

  /**
   * Authenticate the user using credentials.
   *
   * @param credentials
   * @returns {Observable<R>}
   */
  public login(credentials: any) {
    this.clearUser();
    this.clearToken();
    return this.api.post('auth', credentials).do(
      (response) => this.authResponseTasks(response)
    );
  }

  /**
   * Use token to verify and get user object from server.
   *
   * @returns {Observable<R>}
   */
  public verify() {
    return this.api.get('auth', { data: { token: this.token } }).do(
      (response) => this.authResponseTasks(response)
    );
  }

  /**
   * Logout user. Successful logout will blacklist the token.
   *
   * @returns {Observable<R>}
   */
  public logout() {
    this.clearUser();

    let lasttoken = this.token;
    this.clearToken();

    if (!lasttoken) {
      return;
    }

    return this.api.delete('auth', { data: { token: lasttoken } }).subscribe();
  }

  private clearUser() {
    this.currentUser = null;
    localStorage.removeItem('currentUser');
  }

  private clearToken() {
    this.token = null;
    localStorage.removeItem('token');
  }

  private authResponseTasks(response: any) {

    if (response.hasOwnProperty('error')) {
      this.clearUser();
      this.clearToken();
    } else {
      // login() and verify() always returns the user
      this.currentUser = response.user;
      localStorage.setItem('currentUser', JSON.stringify(response.user));

      if (response.token) {
        this.token = response.token;
        localStorage.setItem('token', response.token);
        this.api.addDefaultHeader('Authorization', 'Bearer ' + response.token);
      } else if (this.token) {
        this.api.addDefaultHeader('Authorization', 'Bearer ' + this.token);
      }
    }
  }
}
