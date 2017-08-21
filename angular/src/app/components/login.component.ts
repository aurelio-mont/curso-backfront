import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { UserService } from '../services/user.service';

@Component({
	selector: 'login',
	templateUrl: '../views/login.html',
	providers: [
		UserService
	]
})
export class LoginComponent implements OnInit{
	public title: string;
	public user;
	public identity;
	public token;

	constructor(
		private _route: ActivatedRoute,
		private _router: Router,
		private _userSrvice: UserService

	){

		this.title = 'Componente login';
		this.user = {
			"email": "",
			"password": "",
			"getHash": "" 
		};
	}

	ngOnInit(){
		console.log('El componente login.component, ha sido cargado!');
		this.logout();
		this.redirectIfIdentity();
	}

	logout(){
		this._route.params.forEach((params: Params) => {
			let logout = +params['id'];
			if (logout == 1) {
				localStorage.removeItem('identity');
				localStorage.removeItem('token');

				this.identity = null;
				this.token = null;

				window.location.href = '/login';
			}
		});
	}

	redirectIfIdentity(){
		let identity = this._userSrvice.getIdentity();
		if (identity != null && identity.sub) {
			this._router.navigate(['/']);
		}
	}

	onSubmit(){
		//console.log(this.user);

		this._userSrvice.signup(this.user).subscribe(
			response => {
				this.identity = response;
				if (this.identity.lenght <= 1) {
					console.log('error en el Servidor');
				}{
					if (!this.identity.status) {
						localStorage.setItem('identity', JSON.stringify(this.identity));
						
						// Obtener token
						this.user.getHash = true;
						this._userSrvice.signup(this.user).subscribe(
							response => {
								this.token = response;
								if (this.identity.lenght <= 1) {
								console.log('error en el Servidor');
							}{
								if (!this.identity.status) {
									localStorage.setItem('token', JSON.stringify(this.token));
									window.location.href = '/';
								}
							}},
							error => {
								console.log(<any> error);
							}				
						);
					}
				}},
				error => {
					console.log(<any> error);
				}
		);
	}
}