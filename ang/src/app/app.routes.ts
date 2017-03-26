import { Routes } from '@angular/router';
import { HomeComponent } from './home';
import { AboutComponent } from './about';
import { MyAccountComponent } from './myaccount';
import { NoContentComponent } from './no-content';
import { OrdersComponent } from './orders';
import { OrderDetailsComponent } from './orderdetails';
import { LoginComponent } from './login';
import { LogoutComponent } from './logout';
import { AuthGuard } from './_guards';
import { DataResolver } from './app.resolver';

export const ROUTES: Routes = [
  { path: '', component: HomeComponent },
  { path: 'home', component: HomeComponent },
  { path: 'login', component: LoginComponent },
  {
    path: 'orders', component: OrdersComponent, canActivate: [AuthGuard],
    resolve: { orders: DataResolver }
  },
  {
    path: 'orderdetails/:ts/:uid', component: OrderDetailsComponent,
    canActivate: [AuthGuard], resolve: { menu: DataResolver }
  },
  {
    path: 'myaccount', component: MyAccountComponent,
    canActivate: [AuthGuard], resolve: { details: DataResolver }
  },
  { path: 'logout', component: LogoutComponent },
  { path: '**', redirectTo: '' }
];
//   { path: '**',    component: NoContentComponent },
