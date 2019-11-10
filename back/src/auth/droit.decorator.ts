import { SetMetadata } from '@nestjs/common';

export const Droit = (droit: string) => SetMetadata('droit', droit);
