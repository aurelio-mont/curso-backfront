import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { UserService } from '../services/user.service';
import { User } from '../models/user';

@Component({
	selector: 'user-edit',
	templateUrl: '../views/user.edit.html',
	providers: [
		UserService
	]
})

export class UserEditComponent implements OnInit {

	public title: string;
	public user: User;
	public status;
	public identity;
	public token;
	
	constructor(
		private _userSrvice: UserService,
		private _route: ActivatedRoute,
		private _router: Router
	){
		this.title = 'Modificar mis datos';
		this.identity = this._userSrvice.getIdentity();
		this.token = this._userSrvice.getToken();
		//this.user = new User(1, "user", "", "", "", "" );
	}

	ngOnInit(){
		if (this.identity == null) { 
			this._router.navigate(['/login']);
		} else {
			this.user = new User(
				this.identity.sub,
				this.identity.role,
				this.identity.name,
				this.identity.surname,
				this.identity.email,
				this.identity.password
				);
		}
	}

	onSubmit(){
		console.log(this.user);
		this._userSrvice.update_user(this.user).subscribe(
			response => {
				this.status = response.status;
				if (this.status != 'success') { 
					this.status = 'error';
				} else {
					localStorage.setItem('identity', JSON.stringify(this.user));
				}
			}, error => {
				console.log(<any> error);
			}
		);
	}
}

