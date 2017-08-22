import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { User } from '../models/user';
import { UserService } from '../services/user.service';

@Component({
	selector: 'register',
	templateUrl: '../views/register.html',
	providers: [
		UserService
	]
})
export class RegisterComponent implements OnInit{
	public title: string;
	public user: User;
	public status

	constructor(
		private _route: ActivatedRoute,
		private _router: Router,
		private _userSrvice: UserService

	){
		this.title = 'Registro';
		this.user = new User(1, "user", "", "", "", "" );
	}

	ngOnInit(){
		console.log('El componente register.component, ha sido cargado!');
	}

	onSubmit(){
		console.log(this.user);
		this._userSrvice.register(this.user).subscribe(
			respponse => {
				this.status = respponse.status;
				if (respponse.status != 'success') {
					this.status = 'error';
				}else{
					this.user = new User(1, "user", "", "", "", "" );
				}
			},
			error => {
				console.log(<any> error);
			}
		);
	}
}