import { BrowserModule } from '@angular/platform-browser';
import { FormsModule } from '@angular/forms';
import { HttpModule } from '@angular/http';
import {
  NgModule,
  ApplicationRef
} from '@angular/core';
import {
  removeNgStyles,
  createNewHosts,
  createInputTransfer
} from '@angularclass/hmr';
import {
  RouterModule,
  PreloadAllModules
} from '@angular/router';

// import { MaterialModule } from '@angular/material';
import { MdCardModule } from '@angular/material/card';
import { MdButtonModule } from '@angular/material/button';
import { MdCheckboxModule } from '@angular/material/checkbox';
import { MdMenuModule } from '@angular/material/menu';
import { MdIconModule } from '@angular/material/icon';
import { MdToolbarModule } from '@angular/material/toolbar';
import { MdTabsModule } from '@angular/material/tabs';
import { MdInputModule } from '@angular/material/input';
// import { MdProgressSpinnerModule } from '@angular/material/progress-spinner';

import 'hammerjs';
import { SimpleNotificationsModule } from 'angular2-notifications';
import {
  Api,
  Auth,
  Broadcaster,
  NavHelper
} from './_services';
import {
  AuthGuard,
} from './_guards';

import { ENV_PROVIDERS } from './environment';
import { ROUTES } from './app.routes';
import { AppComponent } from './app.component';
import { APP_RESOLVER_PROVIDERS } from './app.resolver';
import { AppState, InternalStateType } from './app.service';
import { HomeComponent } from './home';
import { AboutComponent } from './about';
import { MyAccountComponent } from './myaccount';
import { NoContentComponent } from './no-content';
import { OrdersComponent } from './orders';
import { OrderDetailsComponent } from './orderdetails';
import { LoginComponent } from './login';
import { LogoutComponent } from './logout';

import './../../node_modules/normalize.css/normalize.css';
import '../styles/cca.scss';

// Application wide providers
const APP_PROVIDERS = [
  Api,
  Auth,
  Broadcaster,
  AuthGuard,
  NavHelper,
  ...APP_RESOLVER_PROVIDERS,
  AppState
];

type StoreType = {
  state: InternalStateType,
  restoreInputValues: () => void,
  disposeOldHosts: () => void
};

@NgModule({
  bootstrap: [AppComponent],
  declarations: [
    AppComponent,
    AboutComponent,
    HomeComponent,
    NoContentComponent,
    OrdersComponent,
    OrderDetailsComponent,
    MyAccountComponent,
    LoginComponent,
    LogoutComponent
  ],
  imports: [ // import Angular's modules
    BrowserModule,
    FormsModule,
    HttpModule,
    RouterModule.forRoot(ROUTES, { useHash: true, preloadingStrategy: PreloadAllModules }),
    // MaterialModule,
    MdCardModule,
    MdButtonModule,
    MdCheckboxModule,
    MdMenuModule,
    MdIconModule,
    MdToolbarModule,
    MdTabsModule,
    MdInputModule,
    // MdProgressSpinnerModule,
    SimpleNotificationsModule.forRoot()
  ],
  providers: [ // expose our Services and Providers into Angular's dependency injection
    ENV_PROVIDERS,
    APP_PROVIDERS
  ]
})
export class AppModule {

  constructor(
    public appRef: ApplicationRef,
    public appState: AppState
  ) { }

  public hmrOnInit(store: StoreType) {
    if (!store || !store.state) {
      return;
    }
    console.log('HMR store', JSON.stringify(store, null, 2));
    // set state
    this.appState._state = store.state;
    // set input values
    if ('restoreInputValues' in store) {
      let restoreInputValues = store.restoreInputValues;
      setTimeout(restoreInputValues);
    }

    this.appRef.tick();
    delete store.state;
    delete store.restoreInputValues;
  }

  public hmrOnDestroy(store: StoreType) {
    const cmpLocation = this.appRef.components.map((cmp) => cmp.location.nativeElement);
    // save state
    const state = this.appState._state;
    store.state = state;
    // recreate root elements
    store.disposeOldHosts = createNewHosts(cmpLocation);
    // save input values
    store.restoreInputValues = createInputTransfer();
    // remove styles
    removeNgStyles();
  }

  public hmrAfterDestroy(store: StoreType) {
    // display new elements
    store.disposeOldHosts();
    delete store.disposeOldHosts;
  }

}
