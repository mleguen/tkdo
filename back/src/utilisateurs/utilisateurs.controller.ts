import { Controller, UseGuards, Get, Param } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import * as moment from 'moment';
import { GetTiragesUtilisateurDTO } from './dto/get-tirages-utilisateur.dto';

// @UseGuards(AuthGuard('jwt'))
@Controller('utilisateurs')
export class UtilisateursController {
  // TODO : DroitGuard(Droit:GET_TIRAGES_PARTICIPANT) - rejet si l'utilisateur n'a pas un des droits fournis
  // TODO : IdUtilisateurParamGuard('id') - rejet si l'id utilisateur n'est pas le même que le param id
  @Get('/:id/tirages')
  getTiragesUtilisateur(@Param('id') id: string): GetTiragesUtilisateurDTO {
    return [
      {
        id: 0,
        titre: "Noël",
        date: moment('25/12', 'DD/MM').format()
      },
      {
        id: 1,
        titre: "Réveillon",
        date: moment('31/12', 'DD/MM').format()
      }
    ];
  }
}
