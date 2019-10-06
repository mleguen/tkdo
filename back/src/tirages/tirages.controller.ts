import { Controller, Get, UseGuards, Param } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';

@UseGuards(AuthGuard('jwt'))
@Controller('tirages')
export class TiragesController {
  // @Get('/:id')
  // getTirage(@Param('id') id: string): GetTirageDTO {
  //   // TODO : le DTO du détail d'un tirage devra retourner les participants complets
  // }
}
