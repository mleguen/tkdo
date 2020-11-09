import { Component, OnInit } from '@angular/core';
import { BackendService } from '../backend.service';

@Component({
  selector: 'app-admin',
  templateUrl: './admin.component.html',
  styleUrls: ['./admin.component.scss']
})
export class AdminComponent /*implements OnInit*/ {
  token = this.backend.token;

  constructor(
    private readonly backend: BackendService
  ) { }

  // ngOnInit(): void {
  // }

}
