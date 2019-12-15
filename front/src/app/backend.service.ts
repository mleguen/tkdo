import { Injectable } from '@angular/core';

import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { environment } from 'src/environments/environment';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class BackendService {

  constructor(
    private http: HttpClient
  ) {}

  delete<T = any>(url: string): Observable<T> {
    return this.http.delete<T>(environment.backUrl + url).pipe(
      catchError<T, Observable<T>>(err => this.handleHttpError(err))
    );
  }

  get<T>(url: string): Observable<T> {
    return this.http.get<T>(environment.backUrl + url).pipe(
      catchError<T, Observable<T>>(err => this.handleHttpError(err))
    );
  }

  post<T>(url: string, body: any | null): Observable<T> {
    return this.http.post<T>(environment.backUrl + url, body).pipe(
      catchError<T, Observable<T>>(err => this.handleHttpError(err))
    );
  }

  private handleHttpError<T>(err: HttpErrorResponse): Observable<T> {
    // Erreur technique
    console.dir(err.error);
    if ((err.error instanceof ErrorEvent) || (err.error.statusCode !== 400)) {
      console.error(err.error.message);
      return throwError(new Error("Erreur d'accès au serveur"));
    }

    // Erreur applicative
    return throwError(err.error);
  }
}
