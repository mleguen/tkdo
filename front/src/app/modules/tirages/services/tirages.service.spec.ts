import { TestBed } from '@angular/core/testing';

import { TiragesService } from './tirages.service';

describe('TiragesService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: TiragesService = TestBed.get(TiragesService);
    expect(service).toBeTruthy();
  });
});
