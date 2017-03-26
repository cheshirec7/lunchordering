import { Injectable } from '@angular/core';
import { Api } from '../../_services';
import { User } from '../../_models';

@Injectable()
export class UserService {

  // users;

  constructor(private api: Api) {
  }

  public getAll() {
    // let options = {search: {name: 'john'}};
    return this.api.get('users'); // .do(
    // (response) => this.authSuccessTasks(response),
    // (error) => this.authFailureTasks(error)
    // );
    // return this.api.get('users').subscribe(
    //   (response) => {
    //     this.users = response.users;
    //   },
    //   (error) => console.error(error)
    // );
  }

  public getById(id: number) {
    // return this.http.get(process.env.API_URL+'users/' + id,
    // this.jwt()).map((response: Response) => response.json());
    // return this.http.get(process.env.API_URL + 'users/' + id + '?token=' +
    // this.token).map((response: Response) => response.json());
  }

  public create(user: User) {
    // return this.http.post(process.env.API_URL+'users', user,
    // this.jwt()).map((response: Response) => response.json());
    // return this.http.post(process.env.API_URL + 'users?token=' +
    // this.token, user).map((response: Response) => response.json());
  }

  public update(user: User) {
    // return this.http.put(process.env.API_URL+'users/' + user.id, user,
    // this.jwt()).map((response: Response) => response.json());
    // return this.http.put(process.env.API_URL + 'users/' + user.id + '?token=' +
    // this.token, user, this.jwt()).map((response: Response) => response.json());
  }

  public delete(id: number) {
    // return this.http.delete(process.env.API_URL+'users/' + id,
    // this.jwt()).map((response: Response) => response.json());
    // return this.http.delete(process.env.API_URL + 'users/' + id + '?token=' +
    // this.token, this.jwt()).map((response: Response) => response.json());
  }

}
