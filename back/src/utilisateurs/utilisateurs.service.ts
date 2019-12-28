import { Injectable } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';

import { IUtilisateur } from '../../../shared/domaine';
import { Utilisateur } from '../../../shared/schema';

@Injectable()
export class UtilisateursService {

  constructor(
    @InjectRepository(Utilisateur) private readonly utilisateurRepository: Repository<Utilisateur>
  ) { }

  readUtilisateurs(): Promise<IUtilisateur[]> {
    return this.utilisateurRepository.find();
  }
}
