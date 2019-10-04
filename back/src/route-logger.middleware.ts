
import { Injectable, NestMiddleware, Logger } from '@nestjs/common';
import { Request, Response } from 'express';

@Injectable()
export class RouteLoggerMiddleware implements NestMiddleware {
  private logger = new Logger(RouteLoggerMiddleware.name);

  use(req: Request, res: Response, next: Function) {
    this.logger.log([req.method, req.url].join(' '));
    next();
  }
}
