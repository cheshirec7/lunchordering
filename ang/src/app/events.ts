export const EVENTS = {
  AUTH: {
    LOGIN_SUCCESS: 'auth-login-success',
    LOGIN_FAILURE: 'auth-login-failed',
    LOGOUT_SUCCESS: 'auth-logout-success',
    LOGOUT_FAILURE: 'auth-logout-failure',
    SESSION_TIMEOUT: 'auth-session-timeout',
  },
  SPINNER: {
    START: 'spinner-started',
    STOP: 'spinner-stop',
    SPINNING: 'spinner-spinning',
  },
  MAINMENU: {
    SETSELECTED: 'mainmenu-set-selected',
  },
  ERROR: {
    DBERROR: 'db-error',
  }
};
