import { Component, OnInit } from '@angular/core';
import { UserService } from './services/user.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css'],
  providers: [
		UserService
	]
})
export class AppComponent {
  public title = 'app';
  public identity;
  public token;

  constructor(private _userSrvice: UserService){
  	this.identity = this._userSrvice.getIdentity();
  	this.token = this._userSrvice.getToken();
  }

  ngOnInit(){
  	console.log("app.component Cargado !");
    console.log(this._userSrvice.getIdentity());
    console.log(this._userSrvice.getToken());
  }
}
